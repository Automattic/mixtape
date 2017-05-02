<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Mixtape' ) ) {
    interface Mixtape_Interfaces_Class_Loader {
        /**
         * @param string $name the class to load
         * @return Mixtape_Interfaces_Class_Loader
         */
        public function load_class( $name );
    }

    class Mixtape_Class_Loader implements Mixtape_Interfaces_Class_Loader {
        /**
         * @var array loaded class map
         */
        private $loaded_classes;
        /**
         * @var string the prefix to use (e.g. Mixtape)
         */
        private $prefix;
        /**
         * @var string the directory for this class structure
         */
        private $base_dir;

        /**
         * Mixtape_Class_Loader constructor.
         * @param array $settings
         * @throws Exception
         */
        public function __construct( $prefix, $base_dir ) {
            $this->loaded_classes  = array();
            $this->prefix          = $prefix;
            $this->base_dir        = $base_dir;
            if ( ! is_dir( $this->base_dir ) ) {
                throw new Exception( 'base_dir does not exist: ' . $this->base_dir );
            }
        }

        public function load_class( $class_name ) {
            $path = $this->get_path_to_class_file( $class_name );
            return $this->include_class_file( $path );
        }

        public function get_path_to_class_file( $class_name ) {
            return path_join( $this->base_dir, $this->class_name_to_relative_path( $class_name ) );
        }

        public function class_name_to_relative_path( $class_name ) {
            $parts = explode( '_', str_replace( $this->prefix, '', $this->strip_prefix( $class_name ) ) );
            return implode( DIRECTORY_SEPARATOR, $parts ) . '.php';
        }

        public function prefixed_class_name( $class_name ) {
            return $this->prefix . '_' . $this->strip_prefix( $class_name );
        }

        private function strip_prefix( $class_name ) {
            return str_replace( $this->prefix, '', $class_name );
        }

        private function include_class_file( $path_to_the_class ) {
            $included = include_once( $path_to_the_class );
            $this->loaded_classes[$path_to_the_class] = $included;

            return $this;
        }
    }

    class Mixtape_Class_Loader_NoConflict implements Mixtape_Interfaces_Class_Loader {
        /**
         * @var bool should we autoload? Defaults to true
         */
        private $should_autoload;
        /**
         * @var string the class prefix
         */
        private $prefix;
        /**
         * @var string this file's dir
         */
        private $base_dir;
        /**
         * @var string the class library dir
         */
        private $lib_dir;
        /**
         * @var string the default prefix
         */
        private $default_prefix = 'Mixtape';
        private $prefix_dir;
        /**
         * @var Mixtape_Class_Loader
         */
        private $class_loader;
        private $is_debugging;

        /**
         * Mixtape_Class_Loader constructor.
         * @param array $settings
         * @throws Exception
         */
        public function __construct( $settings ) {
            $this->is_debugging    = isset( $settings['debug'] ) ? (bool)$settings['debug'] : false;
            $this->prefix          = isset( $settings['prefix'] ) ? $settings['prefix'] : $this->default_prefix;
            $this->base_dir        = isset( $settings['base_dir'] ) ? $settings['base_dir'] : dirname( __FILE__ );
            $this->should_autoload = isset( $settings['should_autoload'] ) ? (bool)$settings['should_autoload'] : true;
            $this->lib_dir         = isset( $settings['lib_dir'] ) ? $settings['lib_dir'] : $this->get_default_lib_dir();
            $this->prefix_dir      = isset( $settings['prefix_dir'] ) ? $settings['prefix_dir'] : $this->get_default_prefix_dir();
            if ( ! is_dir( $this->prefix_dir ) ) {
                wp_mkdir_p( $this->prefix_dir );
            }
            $this->class_loader    = new Mixtape_Class_Loader( $this->prefix, $this->prefix_dir );
        }

        private function get_default_lib_dir() {
            $lib_dir = $this->base_dir . DIRECTORY_SEPARATOR . 'lib';

            return $lib_dir;
        }

        private function get_default_prefix_dir() {
            if ( $this->has_custom_prefix() ) {
                return $this->base_dir . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . strtolower( $this->prefix );
            } else {
                return $this->lib_dir;
            }
        }
        private function has_custom_prefix() {
            return $this->prefix !== $this->default_prefix;
        }

        public function load_class( $name ) {
            $path = $this->class_loader->get_path_to_class_file( $name );

            if ( $this->has_custom_prefix() && !file_exists( $path ) || $this->is_debugging ) {
                $dir_to_create = dirname( $path );
                if ( wp_mkdir_p( $dir_to_create ) ) {
                    $template_path = $this->get_default_prefix_class_path( $name );
                    if ( !is_file( $template_path ) ) {
                        throw new Exception( 'Template path does not exist: ' . $template_path );
                    }
                    $content = str_replace( $this->default_prefix, $this->prefix, file_get_contents( $template_path ) );
                    @file_put_contents( $path, $content );
                }
            }
            $this->class_loader->load_class( $name );
            return $this;
        }

        public function prefixed_class_name( $class_name ) {
            return $this->class_loader->prefixed_class_name( $class_name );
        }

        private function get_default_prefix_class_path( $name ) {
            return path_join( $this->lib_dir, $this->class_loader->class_name_to_relative_path( $name ) );
        }
    }

    /**
     * Class Mixtape
     * This is the entry point to all mixtape functionality
     * It Bootstraps Everything and provides the Environment instance, for further setup
     * - Loads Classes from the given library path, with a given prefix
     * - Creates the environment instance
     */
    class Mixtape {

        /**
         * @var null|object the Environment implementation
         */
        private $environment = null;

        /**
         * @var null|Mixtape_Class_Loader
         */
        private $class_loader = null;

        /**
         * Mixtape constructor.
         * Bootstrap mixtape
         * @param array $settings the settings to use
         * @throws Exception
         */
        function __construct( $settings ) {
            $this->class_loader = new Mixtape_Class_Loader_NoConflict( $settings );
        }

        /**
         * @param array|null $settings
         * @return Mixtape
         */
        public static function create( $settings = null ) {
            if ( null === $settings ) {
                $settings = array(
                    'prefix' => 'Mixtape',
                    'base_dir' => untrailingslashit( dirname( __FILE__ ) ),
                    'is_debugging' => false,
                );
            }
            return new self( $settings );
        }

        /**
         * Register an spl_autoload_register autoloader unless explicitly disabled
         * Falls back to ::load()
         * @return $this
         */
        function register_autoload() {
            if ( function_exists( 'spl_autoload_register' ) ) {
                spl_autoload_register( array( $this->class_loader(), 'load_class' ), true );
            }
            return $this;
        }

        /**
         * Load all Mixtape classes at once.
         * @return $this
         * @throws Exception
         */
        function load() {
            $this->class_loader()
                ->load_class( 'Interfaces_Hookable' )
                ->load_class( 'Interfaces_Data_Store' )
                ->load_class( 'Interfaces_Model_Collection' )
                ->load_class( 'Exception' )
                ->load_class( 'Environment' )
                ->load_class( 'Rest_Api_Controller' )
                ->load_class( 'Rest_Api_Controller_Bundle' );
            return $this;
        }

        /**
         * @return Mixtape_Class_Loader
         */
        function class_loader() {
            return $this->class_loader;
        }

        /**
         * get the Environment for this prefix. Uses ReflectionClass
         * @return Mixtape_Environment
         */
        public function environment() {
            if ( null === $this->environment ) {
                $this->environment = $this->create_instance_of( 'Environment', array( $this ) );
            }
            return $this->environment;
        }

        public function create_instance_of( $class_name, $args = array() ) {
            $full_class_name = $this->class_loader()->prefixed_class_name( $class_name );
            $reflection = new ReflectionClass( $full_class_name );
            return $reflection->newInstanceArgs( $args );
        }

    }
}


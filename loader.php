<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Mixtape' ) ) {

    /**
     * Class Mixtape
     * This is the entry point to all mixtape functionality
     * It Bootstraps Everything and provides the Environment instance, for further setup
     * - Loads Classes from the given library path, with a given prefix
     * - Creates the environment instance
     */
    class Mixtape {
        /**
         * @var array loaded class map
         */
        private $loaded_classes;
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
        /**
         * @var null|object the Environment implementation
         */
        private $environment = null;
        /**
         * @var bool should we autoload? Defaults to true
         */
        private $should_autoload;

        /**
         * Mixtape constructor.
         * Bootstrap mixtape
         * @param array $settings the settings to use
         * @throws Exception
         */
        private function __construct( $settings ) {
            $this->loaded_classes = array();
            $this->is_debugging = isset( $settings['debug'] ) ? (bool)$settings['debug'] : false;
            $this->prefix = isset( $settings['prefix'] ) ? $this->default_prefix : $settings['prefix'];
            $this->base_dir = isset( $settings['base_dir'] ) ? $settings['base_dir'] : dirname( __FILE__ );
            $this->lib_dir = $this->base_dir . DIRECTORY_SEPARATOR . 'lib';
            $this->should_autoload = isset( $settings['should_autoload'] ) ? (bool)$settings['should_autoload'] : true;
            if ( ! is_dir( $this->lib_dir ) ) {
                throw new Exception( 'lib_dir does not exist: ' . $this->lib_dir );
            }
            if ( $this->has_custom_prefix() ) {
                $this->prefix_dir = $this->base_dir . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . strtolower( $this->prefix );
            } else {
                $this->prefix_dir = $this->lib_dir;
            }
        }

        /**
         * @param $settings
         * @return Mixtape
         */
        public static function create( $settings ) {
            return new self( $settings );
        }

        /**
         * Load all Mixtape classes at once.
         * @return $this
         * @throws Exception
         */
        function load() {
            $this
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
         * Register an spl_autoload_register autoloader unless explicitly dibabled
         * Falls back to ::load()
         * @return $this
         */
        function register_autoload() {
            if ( function_exists( 'spl_autoload_register' ) && $this->should_autoload ) {
                spl_autoload_register( array( $this, 'load_class' ), true );
            } else {
                $this->load();
            }
            return $this;
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
            $full_class_name = $this->prefixed_class_name( $class_name );
            $reflection = new ReflectionClass( $full_class_name );
            return $reflection->newInstanceArgs( $args );
        }

        private function include_class_file( $path_to_the_class ) {
            $included = include_once( $path_to_the_class );
            $this->loaded_classes[$path_to_the_class] = $included;

            return $this;
        }

        private function get_path_to_class_file( $class_name ) {
            if (!$this->has_custom_prefix()) {
                return $this->get_default_prefix_class_path( $class_name );
            }
            return path_join( $this->prefix_dir, $this->class_name_to_file( $class_name ) );
        }

        private function get_default_prefix_class_path( $class_name ) {
            return path_join( $this->lib_dir, $this->class_name_to_file( $class_name ) );
        }

        private function class_name_to_file( $class_name ) {
            $parts = explode( '_', str_replace( $this->prefix, '', $class_name ) );
            return implode( DIRECTORY_SEPARATOR, $parts ) . '.php';
        }

        private function has_custom_prefix() {
            return $this->prefix !== $this->default_prefix;
        }

        public function load_class( $class_name ) {
            $path = $this->get_path_to_class_file( $this->strip_prefix( $class_name ) );

            if ( $this->has_custom_prefix() ) {
                if ( $this->is_debugging || !file_exists( $path ) ) {
                    $dir_to_create = dirname( $path );
                    if ( wp_mkdir_p( $dir_to_create ) ) {
                        $template_path = $this->get_default_prefix_class_path( $class_name );
                        if ( !is_file( $template_path ) ) {
                            throw new Exception( 'Template path does not exist: ' . $template_path );
                        }
                        $content = str_replace( $this->default_prefix, $this->prefix, file_get_contents( $template_path ) );
                        @file_put_contents( $path, $content );
                    }
                }
            }

            return $this->include_class_file( $path );
        }

        private function strip_prefix( $class_name ) {
            return str_replace( $this->prefix, '', $class_name );
        }

        public function prefixed_class_name( $class_name ) {
            return $this->prefix . '_' . $this->strip_prefix( $class_name );
        }
    }
}


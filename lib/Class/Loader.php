<?php

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
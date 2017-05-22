<?php
/**
 * Default Mixtape_Interfaces_Class_Loader Implementation
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_Class_Loader
 */
class Mixtape_Class_Loader implements Mixtape_Interfaces_Class_Loader {
	/**
	 * Keep a record of classes loaded
	 *
	 * @var array The loaded class map.
	 */
	private $loaded_classes;
	/**
	 * The prefix to use (e.g. Mixtape)
	 *
	 * @var string
	 */
	private $prefix;
	/**
	 * The directory the loader looks for classes.
	 *
	 * @var string
	 */
	private $base_dir;

	/**
	 * Mixtape_Class_Loader constructor.
	 *
	 * @param string $prefix The prefix to use (e.g. Mixtape).
	 * @param string $base_dir The directory the loader looks for classes.
	 *
	 * @throws Exception Throws if an invalid directory is provided.
	 */
	public function __construct( $prefix, $base_dir ) {
		$this->loaded_classes  = array();
		$this->prefix          = $prefix;
		$this->base_dir        = $base_dir;
		if ( ! is_dir( $this->base_dir ) ) {
			throw new Exception( 'base_dir does not exist: ' . $this->base_dir );
		}
	}

	/**
	 * Loads a class
	 *
	 * @param string $class_name The class to load.
	 *
	 * @return Mixtape_Interfaces_Class_Loader
	 * @throws Exception Throws in case include_class_file fails.
	 */
	public function load_class( $class_name ) {
		$path = $this->get_path_to_class_file( $class_name );
		return $this->include_class_file( $path );
	}

	/**
	 * Get path_to_class_file
	 *
	 * @param string $class_name The class.
	 *
	 * @return string The full path to the file.
	 */
	public function get_path_to_class_file( $class_name ) {
		return path_join( $this->base_dir, $this->class_name_to_relative_path( $class_name ) );
	}

	/**
	 * Class_name_to_relative_path
	 *
	 * @param string $class_name The class name.
	 *
	 * @return string
	 */
	public function class_name_to_relative_path( $class_name ) {
		$parts = explode( '_', str_replace( $this->prefix, '', $this->strip_prefix( $class_name ) ) );
		return implode( DIRECTORY_SEPARATOR, $parts ) . '.php';
	}

	/**
	 * Prefixed_class_name
	 *
	 * @param string $class_name The class name.
	 *
	 * @return string
	 */
	public function prefixed_class_name( $class_name ) {
		return $this->prefix . '_' . $this->strip_prefix( $class_name );
	}

	/**
	 * Strip_prefix
	 *
	 * @param string $class_name The class name.
	 *
	 * @return string
	 */
	private function strip_prefix( $class_name ) {
		return str_replace( $this->prefix, '', $class_name );
	}

	/**
	 * Include_class_file
	 *
	 * @param string $path_to_the_class The file path.
	 *
	 * @return string
	 * @throws Exception Throws when the file does not exist.
	 */
	private function include_class_file( $path_to_the_class ) {
		if ( isset( $this->loaded_classes[ $path_to_the_class ] ) ) {
			return $this;
		}
		if ( ! file_exists( $path_to_the_class ) ) {
			throw new Exception( $path_to_the_class . ' not found' );
		}
		$included = include_once( $path_to_the_class );
		$this->loaded_classes[ $path_to_the_class ] = $included;

		return $this;
	}
}

<?php
/**
 * A Class Loader Interface.
 *
 * Injected into the Bootstrap. Handles all class loading.
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface MT_Interfaces_Classloader
 */
interface MT_Interfaces_Classloader {
	/**
	 * Load a class
	 *
	 * @param string $name The class to load.
	 * @return MT_Interfaces_Classloader
	 */
	function load_class( $name );
}

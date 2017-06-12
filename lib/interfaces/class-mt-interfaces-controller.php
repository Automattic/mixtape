<?php
/**
 * Our controller Interface
 *
 * @package Mixtape/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface MT_Interfaces_Controller
 */
interface MT_Interfaces_Controller {
	/**
	 * Register This Controller
	 *
	 * @param MT_Controller_Bundle $bundle The bundle to register with.
	 * @param MT_Environment       $environment The Environment to use.
	 * @throws MT_Exception Throws.
	 *
	 * @return bool|WP_Error true if valid otherwise error.
	 */
	function register( $bundle, $environment );
}

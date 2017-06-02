<?php
/**
 * Controller Bundle
 *
 * A collection of MT_Rest_Api_Controller, sharing a common prefix.
 *
 * @package Mixtape/REST
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface MT_Interfaces_Rest_Api_Controller_Bundle
 */
interface MT_Interfaces_Controller_Bundle {

	/**
	 * Register REST Routes
	 *
	 * @return mixed
	 */
	public function register();

	/**
	 * Get all the Endpoints
	 *
	 * @return mixed
	 */
	public function get_endpoints();

	/**
	 * Get the Prefix
	 *
	 * @return string
	 */
	public function get_bundle_prefix();
}

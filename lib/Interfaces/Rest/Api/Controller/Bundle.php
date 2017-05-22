<?php
/**
 * Controller Bundle
 *
 * A collection of Mixtape_Rest_Api_Controller, sharing a common prefix.
 *
 * @package Mixtape/REST
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface Mixtape_Interfaces_Rest_Api_Controller_Bundle
 */
interface Mixtape_Interfaces_Rest_Api_Controller_Bundle {
	/**
	 * Start This
	 *
	 * @return mixed
	 */
	public function start();

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

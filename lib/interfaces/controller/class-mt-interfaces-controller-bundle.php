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
interface MT_Interfaces_Controller_Bundle extends MT_Interfaces_Registrable {

	/**
	 * Get the Prefix
	 *
	 * @return string
	 */
	public function get_prefix();
}

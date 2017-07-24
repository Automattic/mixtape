<?php
/**
 * Handle Permissions for a REST Controller Action
 *
 * @package Mixtape/REST
 */

/**
 * Interface Mixtape_Interfaces_Rest_Api_Permissions_Provider
 */
interface MT_Interfaces_Permissions_Provider {
	/**
	 * Handle Permissions for a REST Controller Action
	 *
	 * @param WP_REST_Request $request The request.
	 * @param string          $action The action (e.g. index, create update etc).
	 * @return bool
	 */
	public static function permissions_check( $request, $action );
}

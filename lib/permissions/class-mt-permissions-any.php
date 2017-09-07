<?php
/**
 * Any Permission
 *
 * @package MT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Permissions_Any
 */
class MT_Permissions_Any implements MT_Interfaces_Permissions_Provider {

	/**
	 * Handle Permissions for a REST Controller Action
	 *
	 * @param WP_REST_Request $request The request.
	 * @param string          $action The action (e.g. index, create update etc).
	 * @return bool
	 */
	public function permissions_check( $request, $action ) {
		return true;
	}
}

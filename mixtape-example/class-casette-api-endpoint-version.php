<?php

class Casette_Api_Endpoint_Version extends MT_Controller {
	/**
	 * Version Endpoint base
	 *
	 * @var string the endpoint base
	 */
	protected $base = '/version';

	/**
	 * Setup
	 */
	public function setup() {
		$this->add_route( '' )
			->add_action( $this->action( 'index', 'get_items' ) );
	}

	/**
	 * Get Items
	 *
	 * @param WP_REST_Request $request Req.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		return $this->ok( array(
			'casette_version' => '0.1.0',
		) );
	}

	/**
	 * Permissions,
	 *
	 * @param WP_REST_Request $request R.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}
}
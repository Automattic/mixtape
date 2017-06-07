<?php
/**
 * A Collection of Controllers, under the same prefix
 *
 * @package MT/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Controller_Bundle
 */
class MT_Controller_Bundle implements MT_Interfaces_Controller_Bundle {

	/**
	 * The prefix of this bundle (required)
	 *
	 * @var string|null
	 */
	protected $bundle_prefix = null;

	/**
	 * Collection of Mixtape_Rest_Api_Controller subclasses
	 *
	 * @var array
	 */
	protected $endpoints = array();

	/**
	 * Register all endpoints
	 *
	 * @return MT_Interfaces_Controller_Bundle $this;
	 */
	function register() {
		MT_Expect::that( null !== $this->bundle_prefix, 'api_prefix should be defined' );
		/**
		 * Add/remove endpoints. Useful for extensions
		 *
		 * @param $endpoints array an array of Mixtape_Rest_Api_Controller
		 * @param $bundle MT_Controller_Bundle the bundle instance
		 * @return array
		 */
		$this->endpoints = (array) apply_filters(
			'mixtape_rest_api_controller_bundle_get_endpoints',
			$this->get_endpoints(),
			$this
		);

		foreach ( $this->endpoints as $endpoint ) {
			$endpoint->register( $this );
		}

		return $this;
	}

	/**
	 * Get endpoints.
	 *
	 * @return array
	 */
	function get_endpoints() {
		return array();
	}

	/**
	 * Get Prefix.
	 *
	 * @return string
	 */
	function get_prefix() {
		return $this->bundle_prefix;
	}
}

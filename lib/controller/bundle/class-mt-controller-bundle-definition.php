<?php
/**
 * A Bundle Definition.
 *
 * @package MT/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Controller_Bundle_Definition
 */
class MT_Controller_Bundle_Definition extends MT_Controller_Bundle {

	/**
	 * Our Endpoint Builders
	 *
	 * @var array
	 */
	private $endpoint_builders;
	/**
	 * Environment.
	 *
	 * @var MT_Environment
	 */
	private $environment;

	/**
	 * MT_Controller_Bundle_Definition constructor.
	 *
	 * @param MT_Environment $environment Env.
	 * @param string         $bundle_prefix Prefix.
	 * @param array          $endpoint_builders Builders.
	 */
	function __construct( $environment, $bundle_prefix, $endpoint_builders ) {
		$this->environment = $environment;
		$this->bundle_prefix = $bundle_prefix;
		$this->endpoint_builders = $endpoint_builders;
	}

	/**
	 * Get Endpoints.
	 *
	 * @return array
	 */
	public function get_endpoints() {
		$endpoints = array();
		foreach ( $this->endpoint_builders as $builder ) {
			/**
			 * A Builder.
			 *
			 * @var MT_Controller_Builder $builder
			 */
			$endpoint = $builder
				->with_bundle( $this )
				->with_environment( $this->environment )
				->build();
			$endpoints[] = $endpoint;
		}
		return $endpoints;
	}
}

<?php
/**
 * Build a Bundle
 *
 * @package MT/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Controller_Bundle_Builder
 */
class MT_Controller_Bundle_Builder implements MT_Interfaces_Builder {

	/**
	 * Prefix.
	 *
	 * @var string
	 */
	private $bundle_prefix;
	/**
	 * Env.
	 *
	 * @var MT_Environment
	 */
	private $environment;
	/**
	 * Endpoint Builders.
	 *
	 * @var array
	 */
	private $endpoint_builders = array();
	/**
	 * Bundle.
	 *
	 * @var MT_Controller_Bundle|null
	 */
	private $bundle = null;

	/**
	 * MT_Controller_Bundle_Builder constructor.
	 *
	 * @param MT_Interfaces_Controller_Bundle|null $bundle Bundle.
	 */
	function __construct( $bundle = null ) {
		$this->bundle = $bundle;
	}

	/**
	 * Build it
	 *
	 * @return MT_Interfaces_Controller_Bundle
	 */
	public function build() {
		if ( is_a( $this->bundle, 'MT_Interfaces_Controller_Bundle' ) ) {
			return $this->bundle;
		}
		return new MT_Controller_Bundle( $this->environment, $this->bundle_prefix, $this->endpoint_builders );
	}

	/**
	 * Prefix.
	 *
	 * @param string $bundle_prefix Prefix.
	 * @return MT_Controller_Bundle_Builder $this
	 */
	public function with_prefix( $bundle_prefix ) {
		$this->bundle_prefix = $bundle_prefix;
		return $this;
	}

	/**
	 * Env.
	 *
	 * @param MT_Environment $env Env.
	 * @return MT_Controller_Bundle_Builder $this
	 */
	public function with_environment( $env ) {
		$this->environment = $env;
		return $this;
	}

	/**
	 * Endpoint.
	 *
	 * Adds a new MT_Controller_Builder to our builders and returns it for further setup.
	 *
	 * @return MT_Controller_Builder
	 */
	public function endpoint() {
		$endpoint = new MT_Controller_Builder();

		$this->endpoint_builders[] = $endpoint->with_environment( $this->environment );
		return $endpoint;
	}
}

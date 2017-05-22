<?php

class Mixtape_Rest_Api_Controller_Bundle_Builder implements Mixtape_Interfaces_Builder {

	private $bundle_prefix;
	private $environment;
	private $endpoint_builders = array();
	/**
	 * @var Mixtape_Rest_Api_Controller_Bundle|null
	 */
	private $bundle = null;

	/**
	 * Mixtape_Rest_Api_Controller_Bundle_Builder constructor.
	 *
	 * @param Mixtape_Interfaces_Rest_Api_Controller_Bundle|null $bundle
	 */
	function __construct( $bundle = null ) {
		$this->bundle = $bundle;
	}

	/**
	 * @return Mixtape_Interfaces_Rest_Api_Controller_Bundle
	 */
	public function build() {
		if ( is_a( $this->bundle, 'Mixtape_Interfaces_Rest_Api_Controller_Bundle' ) ) {
			return $this->bundle;
		}
		return new Mixtape_Rest_Api_Controller_Bundle_Definition( $this->environment, $this->bundle_prefix, $this->endpoint_builders );
	}

	/**
	 * @param $bundle_prefix
	 * @return Mixtape_Rest_Api_Controller_Bundle_Builder $this
	 */
	public function with_prefix( $bundle_prefix ) {
		$this->bundle_prefix = $bundle_prefix;
		return $this;
	}

	/**
	 * @param $env
	 * @return Mixtape_Rest_Api_Controller_Bundle_Builder $this
	 */
	public function with_environment( $env ) {
		$this->environment = $env;
		return $this;
	}

	/**
	 * @return Mixtape_Rest_Api_Controller_Builder
	 */
	public function endpoint() {
		$endpoint = new Mixtape_Rest_Api_Controller_Builder();

		$this->endpoint_builders[] = $endpoint->with_environment( $this->environment );
		return $endpoint;
	}
}

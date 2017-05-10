<?php

class Mixtape_Rest_Api_Controller_Bundle_Builder implements Mixtape_Interfaces_Builder {

    private $bundle_prefix;
    private $environment;
    private $endpoint_builders = array();

    /**
     * @return Mixtape_Rest_Api_Controller_Bundle_Definition
     */
    public function build() {
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
     * @param $endpoint
     * @return Mixtape_Rest_Api_Controller_Bundle_Builder $this
     */
    public function add_endpoint( $endpoint ) {
        $this->endpoint_builders[] = $endpoint;
        return $this;
    }
}
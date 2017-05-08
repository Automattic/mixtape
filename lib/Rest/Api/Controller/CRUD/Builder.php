<?php

class Mixtape_Rest_Api_Controller_CRUD_Builder implements Mixtape_Interfaces_Builder {

    private $bundle;
    private $model_definition;
    private $environment;
    private $base;

    public function build() {
        return new Mixtape_Rest_Api_Controller_CRUD( $this->bundle, $this->base, $this->model_definition );
    }

    public function with_bundle( $bundle_prefix ) {
        $this->bundle = $bundle_prefix;
        return $this;
    }

    public function with_model_definition($env ) {
        $this->model_definition = $env;
        return $this;
    }
    public function with_environment( $env ) {
        $this->environment = $env;
        return $this;
    }

    public function with_base( $base ) {
        $this->base = $base;
        return $this;
    }
}
<?php

class Mixtape_Rest_Api_Controller_Builder implements Mixtape_Interfaces_Builder {

    private $controller_class;
    private $environment;
    private $bundle;

    public function with_bundle( $bundle_prefix ) {
        $this->bundle = $bundle_prefix;
        return $this;
    }

    public function with_environment( $env ) {
        $this->environment = $env;
        return $this;
    }

    public function with_class( $controller_class ) {
        $this->controller_class = $controller_class;
        return $this;
    }

    public function build() {
        $class = $this->controller_class;
        return new $class($this->bundle, $this->environment);
    }
}
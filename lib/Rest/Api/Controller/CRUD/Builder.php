<?php

class Mixtape_Rest_Api_Controller_CRUD_Builder implements Mixtape_Interfaces_Builder {

    private $bundle = null;
    private $model_definition = null;
    private $environment = null;
    private $base = '';
    private $actions = array();
    private $class_name = 'Mixtape_Rest_Api_Controller_CRUD';

    public function build() {
        $class_name = $this->class_name;
        if (!class_exists( $class_name ) ) {
            throw new Mixtape_Exception('class ' . $class_name . ' does not exist' );
        }
        return new $class_name( $this->bundle, $this->base, $this->model_definition, $this->actions );
    }

    public function with_class_name($class_name) {
        if (!class_exists( $class_name ) ) {
            throw new Mixtape_Exception('class ' . $class_name . ' does not exist' );
        }
        $this->class_name = $class_name;
        return $this;
    }

    public function with_actions($actions) {
        $this->actions = $actions;
        return $this;
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
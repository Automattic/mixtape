<?php

class Mixtape_Model_Definition_Builder {

    private $data_store = null;
    private $environment = null;
    private $model_class = null;

    function __construct() {
        $this->data_store = new Mixtape_Data_Store_Nil();
    }

    function with_environment( $environment ) {
        $this->environment = $environment;
        return $this;
    }

    function with_model_class( $model_class ) {
        $this->model_class = $model_class;
        return $this;
    }

    function with_data_store( $data_store ) {
        $this->data_store = $data_store;
        return $this;
    }

    function build() {
        return new Mixtape_Model_Definition( $this->environment, $this->model_class, $this->data_store );
    }
}
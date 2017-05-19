<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Model_Definition_Builder implements Mixtape_Interfaces_Builder {
    private $declaration;
    private $data_store;
    private $environment;

    function __construct() {
        $this->with_data_store( new Mixtape_Data_Store_Nil() );
    }

    /**
     * @param Mixtape_Interfaces_Model_Declaration $declaration
     * @return Mixtape_Model_Definition_Builder $this
     */
    function with_declaration( $declaration ) {
        if ( is_string( $declaration ) && class_exists( $declaration ) ) {
            $declaration = new $declaration();
        }
        Mixtape_Expect::is_a( $declaration, 'Mixtape_Interfaces_Model_Declaration' );
        $this->declaration = $declaration;
        return $this;
    }

    /**
     * @param null|Mixtape_Interfaces_Builder $data_store
     * @return Mixtape_Model_Definition_Builder $this
     */
    function with_data_store( $data_store = null ) {
        $this->data_store = $data_store;
        return $this;
    }

    /**
     * @param Mixtape_Environment $environment
     * @return Mixtape_Model_Definition_Builder $this
     */
    function with_environment( $environment ) {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return Mixtape_Model_Definition
     */
    function build() {
        return new Mixtape_Model_Definition($this->environment, $this->declaration, $this->data_store );
    }
}
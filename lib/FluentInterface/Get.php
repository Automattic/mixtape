<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_FluentInterface_Get {
    /**
     * @var Mixtape_Environment
     */
    private $environment;

    function __construct( $environment ) {
        $this->environment = $environment;
    }

    /**
     * @param $type
     * @return Mixtape_Interfaces_Type
     * @throws Mixtape_Exception
     */
    function type( $type ) {
        return $this->environment->type()->definition( $type );
    }

    /**
     * @param $class
     * @return Mixtape_Model_Definition
     * @throws Mixtape_Exception
     */
    function model( $class ) {
        return $this->environment->model_definition( $class );
    }

    function bundle( $prefix ) {
    }
}
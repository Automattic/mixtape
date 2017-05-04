<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Environment {
    /**
     * @var array
     */
    protected $rest_api_bundles;
    /**
     * @var array
     */
    protected $model_definitions;
    /**
     * @var bool
     */
    private $started;
    /**
     * @var Mixtape
     */
    private $main;

    /**
     * Mixtape_Environment constructor.
     * @param $mixtape Mixtape
     */
    public function __construct( $main ) {
        $this->main = $main;
        $this->started = false;
        $this->rest_api_bundles = array();
        $this->model_definitions = array();
    }

    /**
     * @param Mixtape_Interfaces_Model_Delegate $delegate
     * @param null|Mixtape_Interfaces_Data_Store $data_store
     * @return $this
     * @throws Mixtape_Exception
     */
    public function define_model( $delegate, $data_store = null ) {
        $interface = $this->get_main()->class_loader()->prefixed_class_name( 'Interfaces_Model_Delegate' );
        if ( !is_a( $delegate, $interface ) ) {
            throw new Mixtape_Exception('add_model_definition requires ' . $interface);
        }
        $definition = new Mixtape_Model_Definition( $this, $delegate, $data_store );
        $key = $definition->get_model_class();
        $this->model_definitions[$key] = $definition;
        return $this;
    }

    /**
     * @param $class
     * @return Mixtape_Model_Definition the definition
     * @throws Mixtape_Exception
     */
    public function model( $class ) {
        if ( !class_exists( $class ) ) {
            throw new Mixtape_Exception( $class . ': does not exist' );
        }
        if ( !isset( $this->model_definitions[$class] ) ) {
            throw new Mixtape_Exception( $class . ' definition does not exist' );
        }
        return $this->model_definitions[$class];
    }

    /**
     * @param $bundle Mixtape_Interfaces_Rest_Api_Controller_Bundle
     * @return $this Mixtape_Environment
     */
    public function add_rest_bundle( $bundle ) {
        $key = $bundle->get_bundle_prefix();
        $this->rest_api_bundles[ $key ] = $bundle;
        return $this;
    }

    /**
     * Start things up
     * @return $this
     */
    public function start() {
        if ( false === $this->started ) {
            do_action( 'mixtape_environment_before_start', $this );
            foreach ( $this->rest_api_bundles as $k => $bundle ) {
                $bundle->start();
            }
            $this->started = true;
            do_action( 'mixtape_environment_after_start', $this );
        }

        return $this;
    }

    public function get_main() {
        return $this->main;
    }
}
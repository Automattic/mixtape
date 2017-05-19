<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Model_Declaration implements Mixtape_Interfaces_Model_Declaration {
    /**
     * @var Mixtape_Model_Definition
     */
    private $model_definition;

    /**
     * @param Mixtape_Model_Definition $def
     * @return Mixtape_Interfaces_Model_Declaration $this
     */
    function set_definition( $def ) {
        $this->model_definition = $def;
        return $this;
    }

    /**
     * @return Mixtape_Model_Definition $def
     */
    function definition() {
        return $this->model_definition;
    }

    function declare_fields( $definition ) {
        throw new Mixtape_Exception( 'Override me: ' . __FUNCTION__ );
    }

    function get_id( $model ) {
        return $model->get( 'id' );
    }

    function set_id( $model, $new_id ) {
        return $model->set( 'id', $new_id );
    }

    function call( $method, $args = array()) {
        if ( is_callable( $method ) ) {
            return $this->perform_call( $method, $args );
        }
        Mixtape_Expect::that( method_exists( $this, $method ), $method . ' does not exist' );
        return $this->perform_call( array( $this, $method ), $args );
    }

    function get_name() {
        return strtolower( get_class( $this ) );
    }

    private function perform_call( $callable, $args ) {
        return call_user_func_array( $callable, $args );
    }
}
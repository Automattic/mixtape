<?php

class Mixtape_Model_Delegate implements Mixtape_Interfaces_Model_Delegate {

    public function declare_fields( $definition ) {
        throw new Mixtape_Exception('implement me');
    }

    public function get_id( $model ) {
        return $model->get( 'id' );
    }

    public function call($method, $args = array()) {
        if ( is_callable( $method ) ) {
            return $this->perform_call( $method, $args );
        }
        if ( ! method_exists( $this, $method ) ) {
            throw new Mixtape_Exception( $method . ' does not exist' );
        }
        return $this->perform_call( array( $this, $method ), $args );
    }

    private function perform_call( $callable, $args ) {
        return call_user_func_array( $callable, $args );
    }

    function as_bool( $model, $key, $value ) {
        return (bool)$value;
    }

    function as_uint( $model,  $key, $value ) {
        return absint( $value );
    }

    function as_nullable_uint( $model, $key, $value ) {
        return ( empty( $value ) && !is_numeric( $value ) ) ? null : $this->as_uint( $model, $key, $value );
    }
}
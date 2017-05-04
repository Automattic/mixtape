<?php

class Mixtape_Model_Delegate implements Mixtape_Interfaces_Model_Delegate {

    public function declare_fields( $definition ) {
        throw new Mixtape_Exception('implement me');
    }

    public function get_id( $model ) {
        return $model->get( 'id' );
    }

    public function call( $method, $model, $args = array() ) {
        if ( !method_exists( $this, $method ) ) {
            throw new Mixtape_Exception( $method . ' does not exist' );
        }
        return call_user_func_array( array( $this, $method ), array_merge( array( $model ), $args ) );
    }

    function as_bool( $model, $value ) {
        return (bool)$value;
    }

    function as_uint( $model,  $value ) {
        return absint( $value );
    }

    function as_nullable_uint( $model, $value ) {
        return ( empty( $value ) && !is_numeric( $value ) ) ? null : $this->as_uint( $model, $value );
    }
}
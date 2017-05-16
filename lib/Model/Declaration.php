<?php

class Mixtape_Model_Declaration implements Mixtape_Interfaces_Model_Declaration {

    public function declare_fields( $definition ) {
        throw new Mixtape_Exception('implement me');
    }

    /**
     * @param Mixtape_Model $model
     * @return mixed|null
     */
    public function get_id( $model ) {
        return $model->get( 'id' );
    }

    public function set_id( $model, $new_id ) {
        return $model->set( 'id', $new_id );
    }

    public function call( $method, $args = array()) {
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

    function as_bool( $value ) {
        return (!empty( $value ) && 'false' !== $value ) ? (bool)$value : false;
    }

    function as_uint( $value ) {
        return absint( $value );
    }

    function as_nullable_uint( $value ) {
        return ( empty( $value ) && !is_numeric( $value ) ) ? null : $this->as_uint( $value );
    }
}
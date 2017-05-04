<?php

class Mixtape_Model_Delegate implements Mixtape_Interfaces_Model_Delegate {

    public function declare_fields($definition)
    {
        throw new Mixtape_Exception('implement me');
    }

    public function call( $method, $model, $args = array() )
    {
        return call_user_func_array( array( $this, $method ), array_merge( array($model), $args ) );
    }
}
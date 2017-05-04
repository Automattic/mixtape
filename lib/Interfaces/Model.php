<?php

interface Mixtape_Interfaces_Model {
    public function get_id();

    /**
     * @param $field_name
     * @return mixed|null
     */
    public function get( $field_name );

    /**
     * @param $field
     * @param $value
     * @return $this;
     */
    public function set( $field, $value );

    /**
     * validates this object instance
     * @throws Mixtape_Exception
     * @return bool|WP_Error true if valid otherwise error
     */
    public function validate();
}
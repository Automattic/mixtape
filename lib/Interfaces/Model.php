<?php

interface Mixtape_Interfaces_Model {
    /**
     * Get this model's primary identifier
     * @return mixed a unique identifier
     */
    public function get_id();

    /**
     * Get a field for this model
     * @param string $field_name
     * @param array $args
     * @return mixed|null
     */
    public function get( $field_name, $args = array() );

    /**
     * Set a field for this model
     * @param string $field
     * @param mixed $value
     * @return Mixtape_Interfaces_Model $this;
     */
    public function set( $field, $value );

    /**
     * Check if this model has a field
     * @param string $field
     * @return bool
     */
    public function has( $field );

    /**
     * validates this object instance
     * @throws Mixtape_Exception
     * @return bool|WP_Error true if valid otherwise error
     */
    public function validate();
}
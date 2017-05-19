<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Mixtape_Interfaces_Model {
    /**
     * Get this model's unique identifier
     * @return mixed a unique identifier
     */
    function get_id();


    /**
     * Set this model's unique identifier
     * @param mixed $new_id
     * @return Mixtape_Model $model this model
     */
    function set_id( $new_id );

    /**
     * Get a field for this model
     * @param string $field_name
     * @param array $args
     * @return mixed|null
     */
    function get( $field_name, $args = array() );

    /**
     * Set a field for this model
     * @param string $field
     * @param mixed $value
     * @return Mixtape_Interfaces_Model $this;
     */
    function set( $field, $value );

    /**
     * Check if this model has a field
     * @param string $field
     * @return bool
     */
    function has( $field );

    /**
     * validates this object instance
     * @throws Mixtape_Exception
     * @return bool|WP_Error true if valid otherwise error
     */
    function validate();

    /**
     * sanitizes this model's field values
     * @throws Mixtape_Exception
     * @return Mixtape_Interfaces_Model
     */
    function sanitize();
}
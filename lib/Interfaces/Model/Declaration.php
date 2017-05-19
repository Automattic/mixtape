<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Mixtape_Interfaces_Model_Declaration {

    /**
     * @param Mixtape_Model_Definition $def
     * @return mixed
     */
    function set_definition( $def );

    /**
     * @return Mixtape_Model_Definition
     */
    function definition();

    /**
     * @param Mixtape_Model_Field_Declaration_Collection_Builder $definition
     * @return array list of Mixtape_Model_Field_Declaration
     */
    function declare_fields( $definition );

    /**
     * Call a method
     * @param string $method
     * @param array $args
     * @return mixed
     */
    function call( $method, $args = array());

    /**
     * Get this model's unique identifier
     * @param Mixtape_Interfaces_Model $model
     * @return mixed
     */
    function get_id( $model );

    /**
     * Set this model's unique identifier
     * @param Mixtape_Interfaces_Model $model
     * @param mixed $id
     * @return Mixtape_Interfaces_Model the model
     */
    function set_id( $model, $id );

    /**
     * @return string this declaration's name
     */
    function get_name();

}
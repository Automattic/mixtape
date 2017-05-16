<?php

interface Mixtape_Interfaces_Model_Declaration {
    /**
     * @param Mixtape_Model_Definition $definition
     * @return array list of Mixtape_Model_Field_Declaration
     */
    public function declare_fields( $definition );

    /**
     * Call a method
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function call( $method, $args = array());

    /**
     * Get this model's unique identifier
     * @param Mixtape_Interfaces_Model $model
     * @return mixed
     */
    public function get_id( $model );

    /**
     * Set this model's unique identifier
     * @param Mixtape_Interfaces_Model $model
     * @param mixed $id
     * @return Mixtape_Interfaces_Model the model
     */
    public function set_id( $model, $id );

}
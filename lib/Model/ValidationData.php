<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Model_ValidationData {
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var Mixtape_Interfaces_Model
     */
    private $model;
    /**
     * @var Mixtape_Model_Field_Declaration
     */
    private $field;

    /**
     * Mixtape_Model_ValidationData constructor.
     * @param mixed $value
     * @param Mixtape_Interfaces_Model $model
     * @param Mixtape_Model_Field_Declaration $field
     */
    public function __construct( $value, $model, $field )
    {
        $this->value = $value;
        $this->model = $model;
        $this->field = $field;
    }


    /**
     * @return mixed $this->value the value that needs validation
     */
    public function get_value()
    {
        return $this->value;
    }

    /**
     * @return Mixtape_Interfaces_Model
     */
    public function get_model()
    {
        return $this->model;
    }

    /**
     * @return Mixtape_Model_Field_Declaration
     */
    public function get_field()
    {
        return $this->field;
    }
}
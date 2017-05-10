<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Mixtape_Data_Store_Abstract implements Mixtape_Interfaces_Data_Store {
    /**
     * @var Mixtape_Interfaces_Model_Declaration
     */
    protected $model_delegate;
    /**
     * @var Mixtape_Model_Definition
     */
    protected $definition;
    /**
     * @var Mixtape_Data_Serializer
     */
    protected $serializer;
    /**
     * @var Mixtape_Data_Mapper
     */
    protected $data_mapper;

    public function __construct( $post_type = null, $args = array() ) {
    }

    /**
     * @param Mixtape_Model_Definition $definition
     * @return Mixtape_Interfaces_Data_Store $this
     */
    public function set_definition( $definition ) {
        $this->definition = $definition;
        $this->model_delegate = $definition->get_delegate();
        $this->serializer = new Mixtape_Data_Serializer( $this->definition );
        $this->data_mapper = new Mixtape_Data_Mapper( $this->definition, $this->serializer );
        return $this;
    }

    /**
     * @return Mixtape_Interfaces_Model_Declaration
     */
    public function get_model_delegate() {
        return $this->model_delegate;
    }

    /**
     * @return Mixtape_Model_Definition
     */
    public function get_definition() {
        return $this->definition;
    }

    /**
     * @return Mixtape_Data_Serializer
     */
    public function get_serializer() {
        return $this->serializer;
    }

    /**
     * @return Mixtape_Data_Mapper
     */
    public function get_data_mapper() {
        return $this->data_mapper;
    }
}
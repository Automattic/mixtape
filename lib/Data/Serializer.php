<?php

class Mixtape_Data_Serializer {
    private $model_delegate;

    /**
     * Mixtape_Data_Serializer constructor.
     * @param Mixtape_Model_Definition $model_definition
     */
    function __construct( $model_definition ) {
        $this->model_delegate = $model_definition->get_delegate();
    }

    /**
     * @param Mixtape_Model_Field_Declaration $field_declaration
     * @param mixed $value
     * @return mixed the deserialized value
     */
    function deserialize( $field_declaration, $value ) {
        $deserializer = $field_declaration->get_deserializer();
        return $deserializer ? $this->model_delegate->call( $deserializer, array( $value ) ) : $value;
    }

    function serialize( $field_declaration, $value ) {
        $serializer = $field_declaration->get_serializer();
        if ( isset( $serializer ) && !empty( $serializer ) ) {
            return $this->model_delegate->call( $serializer, array( $value ) );
        }
        return $value;
    }
}
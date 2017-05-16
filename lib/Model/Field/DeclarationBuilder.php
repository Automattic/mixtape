<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Mixtape_Model_Field_DeclarationBuilder
 * Builds a Mixtape_Model_Field_Declaration
 */
class Mixtape_Model_Field_DeclarationBuilder {
    function __construct() {
        $this->args = array(
            'name'              => '',
            'type'              => Mixtape_Model_Field_Types::FIELD,
            'required'          => false,
            'map_from'          => null,
            'before_return'     => null,
            'sanitize'          => null,
            'on_serialize'      => null,
            'on_deserialize'    => null,
            'value_type'        => 'any',
            'default_value'     => null,
            'json_name'         => null,
            'supported_outputs' => array( 'json' ),
            'description'       => null,
            'validations'       => array(),
            'choices'           => null,
        );
    }
    public function build() {
        return new Mixtape_Model_Field_Declaration( $this->args );
    }

    public function with_default($default_value ) {
        return $this->set( 'default_value', $default_value );
    }

    public function named($name ) {
        return $this->set( 'name', $name );
    }

    public function with_field_type($type ) {
        return $this->set( 'type', $type );
    }

    public function map_from( $mapped_from ) {
        return $this->set( 'map_from', $mapped_from );
    }

    public function with_sanitize($sanitize ) {
        return $this->set( 'sanitize', $sanitize );
    }

    public function with_serializer( $before_save ) {
        return $this->set( 'on_serialize', $before_save );
    }

    public function with_deserializer( $before_save ) {
        return $this->set( 'on_deserialize', $before_save );
    }

    public function required( $required = true ) {
        return $this->set( 'required', $required );

    }

    public function with_supported_outputs( $supported_outputs = array() ) {
        return $this->set( 'supported_outputs', (array)$supported_outputs );
    }

    public function not_visible() {
        return $this->with_supported_outputs( array() );
    }

    public function of_type($value_type ) {
        return $this->set( 'value_type', $value_type );
    }

    public function dto_name($json_name ) {
        return $this->set( 'json_name', $json_name );
    }

    public function with_description( $description ) {
        return $this->set( 'description', $description );
    }

    public function with_validations( $validations ) {
        return $this->set( 'validations', is_array( $validations ) ? $validations : array( $validations ) );
    }

    private function set( $name, $value ) {
        $this->args[$name] = $value;
        return $this;
    }

    public function choices( $choices ) {
        return $this->set( 'choices', is_array( $choices ) ? $choices : array( $choices ) );
    }
}
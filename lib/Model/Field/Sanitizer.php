<?php

class Mixtape_Model_Field_Sanitizer {
    private $sanitization_functions = array();

    function __construct( $sanitization_functions = array() ) {
        $this->sanitization_functions = array_merge( array(
            'string'       => 'sanitize_text_field',
            'email'        => 'sanitize_email',
            'int'          => 'intval',
            'uint'         => 'absint',
            'float'        => 'cast_float',
            'array:int'    => array( $this, 'map_type' ),
            'array:uint'   => array( $this, 'map_type' ),
            'array:string' => array( $this, 'map_type' )
        ), $sanitization_functions );
    }

    function sanitize( $field_declaration, $value ) {
        $field_value_type = $field_declaration->get_value_type();
        $sanitization_function = isset( $this->sanitization_functions[$field_value_type] ) ? $this->sanitization_functions[$field_value_type] : false;
        if (false !== $sanitization_function) {
            $value = call_user_func( $sanitization_function, $value );
            return $value;
        }
        return $value;
    }

    function map_type( $value ) {
        return $value;
    }
}
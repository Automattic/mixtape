<?php

class Mixtape_Field_Type_String implements Mixtape_Interfaces_Model_Field_Type {
    function get_type() {
        return 'string';
    }

    function sanitize( $value ) {
        return sanitize_text_field( $value );
    }

    function escape( $value ) {
        return $value;
    }
}
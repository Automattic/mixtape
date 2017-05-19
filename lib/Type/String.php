<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Type_String extends Mixtape_Type {
    function __construct() {
        parent::__construct( 'string' );
    }

    function sanitize( $value ) {
        return sanitize_text_field( $value );
    }

    function default_value() {
        return '';
    }

    function cast( $value ) {
        return (string)$value;
    }
}
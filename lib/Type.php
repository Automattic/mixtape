<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Type implements Mixtape_Interfaces_Type {
    protected $identifier;

    function __construct( $identifier ) {
        $this->identifier = $identifier;
    }

    function name() {
        return $this->identifier;
    }

    function default_value() {
        return null;
    }

    function cast( $value ) {
        return $value;
    }

    function sanitize($value) {
        return $value;
    }

    function schema() {
        return array(
            'type' => $this->name()
        );
    }

    static function any() {
        return new self( 'any' );
    }
}
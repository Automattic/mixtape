<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Mixtape_Interfaces_Type {
    public function cast( $value );
    public function default_value();
    public function name();
    public function sanitize( $value );
    public function schema();
}
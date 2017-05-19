<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Mixtape_Interfaces_Class_Loader {
    /**
     * @param string $name the class to load
     * @return Mixtape_Interfaces_Class_Loader
     */
    function load_class( $name );
}
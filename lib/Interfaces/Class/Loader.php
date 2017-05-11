<?php

interface Mixtape_Interfaces_Class_Loader {
    /**
     * @param string $name the class to load
     * @return Mixtape_Interfaces_Class_Loader
     */
    public function load_class( $name );
}
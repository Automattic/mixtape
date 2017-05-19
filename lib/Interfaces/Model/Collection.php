<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Mixtape_Interfaces_Model_Collection {
    /**
     * @return Iterator
     */
    function get_items();
}
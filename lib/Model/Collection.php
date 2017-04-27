<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Mixtape_Model_Collection
 * represents a collection of Mixtape_Model
 */
class Mixtape_Model_Collection implements Mixtape_Interfaces_Model_Collection {
    /**
     * @var array
     */
    private $models;

    public function __construct($models = array() ) {
        $this->models = $models;
    }

    public function get_items() {
        return new ArrayIterator( $this->models );
    }
}
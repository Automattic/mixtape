<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Mixtape_Rest_Api_Controller_Bundle
 * Represents a collection of Mixtape_Rest_Api_Controller instances, sharing a common prefix
 * @package rest-api
 */
class Mixtape_Rest_Api_Controller_Bundle implements Mixtape_Hookable {

    /**
     * @var string|null the prefix of this bundle
     */
    protected $bundle_prefix = null;
    /**
     * @var array collection of Mixtape_Rest_Api_Controller subclasses
     */
    private $endpoints = array();

    /**
     * Mixtape_Rest_Api_Controller_Bundle constructor.
     */
    public function __construct() {
    }

    public function start() {
        if ( null === $this->bundle_prefix ) {
            throw new Mixtape_Exception( 'api_prefix should be defined' );
        }
        add_action( 'rest_api_init', array( $this, 'register' ) );
        return $this;
    }

    /**
     * bootstrap registry
     * register all endpoints
     */
    public function register() {
        if ( !$this->can_use_rest_api() ) {
            return;
        }
        $rest_api_prefix = str_replace('/', '_', $this->bundle_prefix );
        /**
         * add/remove endpoints. Useful for extensions
         * @param $endpoints array an array of Mixtape_Rest_Api_Controller
         * @param $bundle Mixtape_Rest_Api_Controller_Bundle the bundle instance
         * @return array
         */
        $this->endpoints = (array)apply_filters( 'mixtape_rest_api_get_endpoints', $this->get_endpoints(), $this );

        foreach ($this->endpoints as $endpoint ) {
            $endpoint->register( $this );
        }
    }

    /**
     * @return Mixtape_Rest_Api_Helper
     */
    public function get_registry() {
        return null;
    }

    protected function get_endpoints() {
        return array();
    }

    protected function can_use_rest_api() {
        return true;
    }

    public function get_bundle_prefix() {
        return $this->bundle_prefix;
    }
}
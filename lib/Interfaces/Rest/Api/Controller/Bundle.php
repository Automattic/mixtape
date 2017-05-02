<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Interface Mixtape_Interfaces_Rest_Api_Controller_Bundle
 * Represents a collection of Mixtape_Rest_Api_Controller instances, sharing a common prefix
 * @package rest-api
 */
interface Mixtape_Interfaces_Rest_Api_Controller_Bundle {
    public function start();

    public function register();

    public function get_endpoints();

    public function get_bundle_prefix();
}
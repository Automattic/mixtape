<?php

class Mixtape_Testing_Controller_TestCase extends Mixtape_Testing_Model_TestCase {
    /**
     * @var WP_REST_Server
     */
    protected $rest_server;

    function setUp() {
        parent::setUp();
        /** @var WP_REST_Server $wp_rest_server */
        global $wp_rest_server;
        $this->rest_server = $wp_rest_server = new WP_REST_Server;
    }
}
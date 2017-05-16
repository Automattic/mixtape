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

    function assert_response_status($response, $status_code) {
        $this->assertEquals( $status_code, $response->get_status() );
    }

    function assert_http_response_status_success( $response ) {
        $this->assert_response_status( $response, Mixtape_Rest_Api_Controller::HTTP_SUCCESS );
    }

    function assert_http_response_status_created( $response ) {
        $this->assert_response_status( $response, Mixtape_Rest_Api_Controller::HTTP_CREATED );
    }

    function assert_http_response_status_not_found( $response ) {
        $this->assert_response_status( $response, Mixtape_Rest_Api_Controller::HTTP_NOT_FOUND );
    }
}
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class Mixtape_Rest_Api_Controller extends WP_REST_Controller {
    const HTTP_CREATED   = 201;
    const HTTP_SUCCESS   = 200;
    const BAD_REQUEST    = 400;
    const HTTP_NOT_FOUND = 404;

    /**
     * @var Mixtape_Rest_Api_Controller_Bundle
     */
    protected $controller_bundle;
    /**
     * @var string the endpoint base
     */
    protected $base = null;

    /**
     * Sensei_REST_API_Controller constructor.
     * @param $controller_bundle Mixtape_Rest_Api_Controller_Bundle
     * @throws Mixtape_Exception
     */
    public function __construct( $controller_bundle = null ) {
        $this->controller_bundle = $controller_bundle;
        if ( empty( $this->base ) ) {
            throw new Mixtape_Exception( 'Need to put a string with a backslash in $base' );
        }
    }

    public function set_controller_bundle( $controller_bundle ) {
        $this->controller_bundle = $controller_bundle;
    }

    protected function add_links( $model ) {
        return array();
    }

    public function register() {
        throw new Mixtape_Exception( 'override me' );
    }

    protected function succeed( $data ) {
        return new WP_REST_Response( $data, self::HTTP_SUCCESS );
    }

    protected function created( $data ) {
        return new WP_REST_Response( $data, self::HTTP_CREATED );
    }

    protected function fail_with( $data ) {
        return new WP_REST_Response( $data, self::BAD_REQUEST );
    }

    protected function not_found( $message ) {
        return $this->respond( new WP_REST_Response( array( 'message' => $message ), self::HTTP_NOT_FOUND) );
    }

    public function respond( $thing ) {
        return rest_ensure_response( $thing );
    }
}
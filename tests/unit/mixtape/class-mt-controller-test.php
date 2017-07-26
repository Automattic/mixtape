<?php
/**
 * MT_ControllerTest
 *
 * @package MT/Tests
 */

/**
 * Class MT_ControllerTest
 */
class MT_ControllerTest extends MT_Testing_TestCase {
	/**
	 * A Controller.
	 *
	 * @var MT_Controller
	 */
	private $controller;

	/**
	 * Set this up
	 */
	function setUp() {
		parent::setUp();
		$this->controller = new MT_Controller();
	}

	/**
	 * Exists
	 * @covers MT_Controller
	 */
	function test_exists() {
		$this->assertClassExists( 'MT_Controller' );
	}

	/**
	 * Structure
	 *
	 * @covers MT_Controller::add_route
	 */
	function test_add_route_return_route() {
		$route = $this->controller->add_route( '/' );
		$this->assertInstanceOf( 'MT_Controller_Route', $route );
	}

	function test_respond_wraps_into_rest_response() {
		$this->assertInstanceOf( 'WP_REST_Response', $this->controller->respond( 'result', 200 ) );
		$this->assertInstanceOf( 'WP_REST_Response', $this->controller->respond( array(), 200 ) );
		$this->assertInstanceOf( 'WP_REST_Response', $this->controller->respond( new WP_Error('err'), 400 ) );
	}
}


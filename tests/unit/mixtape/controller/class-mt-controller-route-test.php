<?php
/**
 * MT_Controller_RouteTest
 *
 * @package MT/Tests
 */

/**
 * Class MT_Controller_RouteTest
 */
class MT_Controller_RouteTest extends MT_Testing_Controller_TestCase {
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
	 * @covers MT_Controller_Route
	 */
	function test_exists() {
		$this->assertClassExists( 'MT_Controller_Route' );
	}

	/**
	 * Throws
	 *
	 * @expectedException MT_Exception
	 * @covers MT_Controller_Route::handler
	 */
	function test_handler_throws_when_invalid_action() {
		$route = new MT_Controller_Route( $this->controller, '/' );
		$route->add_action( $this->controller->action( 'foobar', array( $this, 'action' ) ) );
	}

	/**
	 * Throws
	 *
	 * @expectedException MT_Exception
	 * @covers MT_Controller_Route::permissions
	 */
	function test_permissions_throws_when_invalid_action() {
		$route = new MT_Controller_Route( $this->controller, '/' );
		$route->add_action( $this->controller->action( 'foobar' )
			->with_permission_callback( array( $this, 'action' ) ) );
	}

	/**
	 * Throws
	 *
	 * @expectedException MT_Exception
	 * @covers MT_Controller_Route::permissions
	 */
	function test_args_throws_when_invalid_action() {
		$route = new MT_Controller_Route( $this->controller, '/' );
		$route->add_action( $this->controller->action( 'foobar' )->with_args_callback( array( $this, 'action' ) ) );
	}

	/**
	 * Throws
	 *
	 * @expectedException MT_Exception
	 * @covers MT_Controller_Route::as_array
	 */
	function test_as_array_throws_when_invalid_handler_callable() {
		$route = new MT_Controller_Route( $this->controller, '/' );
		$route->add_action( $this->controller->action( 'index', 'INVALID' ) );
		$route->as_array();
	}

	/**
	 * Throws
	 *
	 * @expectedException MT_Exception
	 * @covers MT_Controller_Route::as_array
	 */
	function test_as_array_throws_when_invalid_permissions_callable() {
		$route = new MT_Controller_Route( $this->controller, '/' );
		$route->add_action( $this->controller->action( 'index', array( $this, 'action' ) )
			->with_permission_callback( 'INVALID' ) );
		$route->as_array();
	}

	/**
	 * Throws
	 *
	 * @expectedException MT_Exception
	 * @covers MT_Controller_Route::as_array
	 */
	function test_as_array_throws_when_invalid_args_callable() {
		$route = new MT_Controller_Route( $this->controller, '/' );
		$route->add_action( $this->controller->action( 'index', array( $this, 'action' ) )
			->with_args_callback( 'INVALID' ) );
		$route->as_array();
	}

	/**
	 * Structure
	 *
	 * @covers MT_Controller_Route::as_array
	 */
	function test_as_array() {
		$c = $this->controller;
		$route = $this->controller->add_route( '/' )
			->add_action(
				$c->action( 'index', array( $this, 'action' ) )
					->with_permission_callback( array( $this, 'action' ) )
					->with_args_callback( array( $this, 'action' ) ) );
		$result = $route->as_array();
		$this->assertNotNull( $result );
		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'pattern', $result );
		$this->assertArrayHasKey( 'actions', $result );

		$this->assertEquals( '/', $result['pattern'] );

		$actions = $result['actions'];
		$this->assertInternalType( 'array', $actions );
		$this->assertEquals( 1, count( $actions ) );

		$action = $actions[0];
		$this->assertEquals( array( $this, 'action' ), $action['callback'] );
		$this->assertEquals( array( $this, 'action' ), $action['permission_callback'] );
		$this->assertEquals( array(), $action['args'] );
		$this->assertEquals( WP_REST_Server::READABLE, $action['methods'] );
	}

	/**
	 * Some callable
	 */
	function action() {
		return array();
	}
}


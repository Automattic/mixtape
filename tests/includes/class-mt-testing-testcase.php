<?php
/**
 * Testcase
 *
 * @package MT/Testing
 */

/**
 * Class MT_Testing_TestCase
 */
class MT_Testing_TestCase extends WP_UnitTestCase {
	/**
	 * Admin
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Default User
	 *
	 * @var int
	 */
	protected $default_user_id;

	/**
	 * Environment
	 *
	 * @var MT_Environment
	 */
	protected $environment;

	/**
	 * Bootstrap
	 *
	 * @var MT_Bootstrap
	 */
	protected $mixtape;

	/**
	 * REST Server
	 *
	 * @var WP_REST_Server
	 */
	protected $rest_server;

	/**
	 * Expect a class Exists.
	 *
	 * @param string $cls Class Name.
	 */
	function assertClassExists( $cls ) {
		$this->assertTrue( class_exists( $cls ), 'Failed Asserting that class ' . $cls . ' exists.' );
	}

	/**
	 * Setup
	 */
	function setUp() {
		parent::setUp();
		if ( ! MT_Bootstrap::is_compatible() ) {
			$this->markTestSkipped( 'Incompatible Testing Environment' );
		}

		$root_path = dirname( MT_Bootstrap::get_base_dir() );
		$this->mixtape = MT_Bootstrap::create()->load();
		$this->environment = $this->mixtape->environment();

		if ( ! class_exists( 'Casette' ) ) {
			include_once( $root_path . DIRECTORY_SEPARATOR . 'mixtape-example' . DIRECTORY_SEPARATOR . 'includes-casette.php' );
		}
		include_once 'class-test-extension-model.php';
		$admin = get_user_by( 'email', 'rest_api_admin_user@test.com' );
		if ( false === $admin ) {
			$this->admin_id = wp_create_user(
				'rest_api_admin_user',
				'rest_api_admin_user',
				'rest_api_admin_user@test.com'
			);
			$admin = get_user_by( 'ID', $this->admin_id );
			$admin->set_role( 'administrator' );
		}

		$this->default_user_id = get_current_user_id();
		$this->login_as_admin();
		/**
		 *The global
		 *
		 * @var WP_REST_Server $wp_rest_server
		 */
		global $wp_rest_server;
		$this->rest_server = new WP_REST_Server;
		$wp_rest_server = $this->rest_server;
	}

	/**
	 * Login as admin
	 *
	 * @return MT_Testing_TestCase
	 */
	function login_as_admin() {
		return $this->login_as( $this->admin_id );
	}

	/**
	 * Login As User
	 *
	 * @param int $user_id User ID.
	 * @return MT_Testing_TestCase $this
	 */
	function login_as( $user_id ) {
		wp_set_current_user( $user_id );
		return $this;
	}

	/**
	 * Expect a model is valid
	 *
	 * @param MT_Interfaces_Model $model The model.
	 */
	function assertModelValid( $model ) {
		$this->assertTrue( $model->validate() );
	}



	/**
	 * Assert Status
	 *
	 * @param WP_REST_Response $response Response.
	 * @param int              $status_code Code.
	 */
	function assert_response_status( $response, $status_code ) {
		$this->assertEquals( $status_code, $response->get_status() );
	}

	/**
	 * Assert Status 200
	 *
	 * @param WP_REST_Response $response Response.
	 */
	function assert_http_response_status_success( $response ) {
		$this->assert_response_status( $response, MT_Controller::HTTP_OK );
	}

	/**
	 * Assert Status 201
	 *
	 * @param WP_REST_Response $response Response.
	 */
	function assert_http_response_status_created( $response ) {
		$this->assert_response_status( $response, MT_Controller::HTTP_CREATED );
	}

	/**
	 * Assert Status 404
	 *
	 * @param WP_REST_Response $response Response.
	 */
	function assert_http_response_status_not_found( $response ) {
		$this->assert_response_status( $response, MT_Controller::HTTP_NOT_FOUND );
	}

	/**
	 * Ensure we got a certain response code
	 *
	 * @param WP_REST_Response $response The Response.
	 * @param int              $status_code Expected status code.
	 */
	function assertResponseStatus( $response, $status_code ) {
		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( $status_code, $response->get_status() );
	}

	/**
	 * Have WP_REST_Server Dispatch an HTTP request
	 *
	 * @param string $endpoint The Endpoint.
	 * @param string $method Http mehod.
	 * @param array  $args Any Data/Args.
	 * @return WP_REST_Response
	 */
	function request( $endpoint, $method, $args = array() ) {
		$request = new WP_REST_Request( $method, $endpoint );
		foreach ( $args as $key => $value ) {
			$request->set_param( $key, $value );
		}
		return $this->rest_server->dispatch( $request );
	}

	/**
	 * Have WP_REST_Server Dispatch a GET HTTP request
	 *
	 * @param string $endpoint The Endpoint.
	 * @param array  $args Any Data/Args.
	 * @return WP_REST_Response
	 */
	function get( $endpoint, $args = array() ) {
		return $this->request( $endpoint, 'GET', $args );
	}

	/**
	 * Have WP_REST_Server Dispatch a POST HTTP request
	 *
	 * @param string $endpoint The Endpoint.
	 * @param array  $args Any Data/Args.
	 * @return WP_REST_Response
	 */
	function post( $endpoint, $args = array() ) {
		return $this->request( $endpoint, 'POST', $args );
	}

	/**
	 * Have WP_REST_Server Dispatch a PUT HTTP request
	 *
	 * @param string $endpoint The Endpoint.
	 * @param array  $args Any Data/Args.
	 * @return WP_REST_Response
	 */
	function put( $endpoint, $args = array() ) {
		return $this->request( $endpoint, 'PUT', $args );
	}

	/**
	 * Have WP_REST_Server Dispatch a DELETE HTTP request
	 *
	 * @param string $endpoint The Endpoint.
	 * @param array  $args Any Data/Args.
	 * @return WP_REST_Response
	 */
	function delete( $endpoint, $args = array() ) {
		return $this->request( $endpoint, 'DELETE', $args );
	}

	function requires_php_53_or_greater() {
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			$this->markTestSkipped( 'Some Mock Fatals issues on php 5.2' );
		}
	}
}

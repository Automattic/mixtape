<?php
/**
 * Tests
 *
 * @package MT/Tests
 */

/**
 * Class MT_Controller_SettingsTest
 */
class MT_Controller_SettingsTest extends MT_Testing_Controller_TestCase {

	/**
	 * Set Up.
	 */
	function setUp() {
		parent::setUp();
		$this->mixtape->environment()->define_model( 'Casette' );
		$env = $this->mixtape->environment();
		$env->define_model( 'CasetteSettings' )
			->with_data_store( new MT_Data_Store_Option( $env->model( 'CasetteSettings' ) ) );

		$bundle = $env->rest_api( 'casette-crud-test/v1' );

		$bundle->add_endpoint( new MT_Controller_Settings( '/settings', 'CasetteSettings' ) );
		$env->auto_start();

		do_action( 'rest_api_init' );
	}

	/**
	 * Test Exists
	 *
	 * @covers MT_Controller_Settings
	 */
	function test_exists() {
		$this->assertClassExists( 'MT_Controller_Settings' );
	}

	/**
	 * Test Registered
	 *
	 * @covers MT_Controller_Settings
	 */
	function test_bundle_route_registered() {
		$response = $this->get( '/casette-crud-test/v1' );

		$this->assertNotNull( $response );
		$this->assertResponseStatus( $response, 200 );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'routes', $data );
		$routes = $data['routes'];
		$this->assertArrayHasKey( '/casette-crud-test/v1/settings', $routes );
		$route = $routes['/casette-crud-test/v1/settings'];
		$this->assertTrue( in_array( 'GET', $route['methods'], true ) );
	}

	/**
	 * Test GET
	 *
	 * @covers MT_Controller_Settings::get_items
	 */
	function test_get_return_all_settings() {
		$response = $this->get( '/casette-crud-test/v1/settings' );
		$this->assertResponseStatus( $response, 200 );
		$data = $response->get_data();

		$this->assertArrayHasKey( 'per_page', $data );
		$this->assertArrayHasKey( 'hide_listened', $data );
		$this->assertArrayHasKey( 'enable_private', $data );
	}

	/**
	 * Test Create
	 *
	 * @covers MT_Controller_Settings::create_item
	 */
	function test_post_settings_status_created() {
		$request_data = array(
			'per_page' => 11,
		);
		$response = $this->post( '/casette-crud-test/v1/settings', $request_data );
		$this->assertResponseStatus( $response, 201 );
	}

	/**
	 * Test Update
	 *
	 * @covers MT_Controller_Settings::create_item
	 */
	function test_update_settings_status_success() {
		$request_data = array(
			'per_page' => 11,
		);
		$response = $this->put( '/casette-crud-test/v1/settings', $request_data );
		$this->assertResponseStatus( $response, 200 );
	}

	/**
	 * Test POST
	 *
	 * @covers MT_Controller_Settings::create_item
	 */
	function test_post_settings_update_value() {
		$model_def = $this->environment->model( 'CasetteSettings' );
		$model = $model_def->get_data_store()->get_entity( null );
		$previous_per_page = $model->get( 'mixtape_casette_per_page' );
		$request_data = array(
			'per_page' => $previous_per_page + 1,
		);
		$response = $this->post( '/casette-crud-test/v1/settings', $request_data );
		$this->assertResponseStatus( $response, 201 );
		$response = $this->get( '/casette-crud-test/v1/settings' );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'per_page', $data );
		$this->assertNotEquals( $previous_per_page, $data['per_page'] );
	}

	/**
	 * Test POST Multiple Values
	 *
	 * @covers MT_Controller_Settings::create_item
	 */
	function test_post_settings_update_multiple_values() {
		$model_def = $this->environment->model( 'CasetteSettings' );
		$model = $model_def->get_data_store()->get_entity( null );
		$previous_per_page = $model->get( 'mixtape_casette_per_page' );
		$previous_enable_private = $model->get( 'mixtape_casette_enable_private' );
		$request_data = array(
			'per_page' => $previous_per_page + 1,
			'enable_private' => ! $previous_enable_private,
		);
		$response = $this->post( '/casette-crud-test/v1/settings', $request_data );
		$this->assertResponseStatus( $response, 201 );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'per_page', $data );
		$this->assertNotEquals( $previous_per_page, $data['per_page'] );
		$this->assertArrayHasKey( 'enable_private', $data );
		$this->assertNotEquals( $previous_enable_private, $data['enable_private'] );

		$response = $this->get( '/casette-crud-test/v1/settings' );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'per_page', $data );
		$this->assertNotEquals( $previous_per_page, $data['per_page'] );
		$this->assertArrayHasKey( 'enable_private', $data );
		$this->assertNotEquals( $previous_enable_private, $data['enable_private'] );
	}

	/**
	 * Test DELETE 404
	 *
	 * @covers MT_Controller_Settings
	 */
	function test_delete_not_found() {
		$response = $this->delete( '/casette-crud-test/v1/settings' );
		$this->assertResponseStatus( $response, 404 );
	}
}


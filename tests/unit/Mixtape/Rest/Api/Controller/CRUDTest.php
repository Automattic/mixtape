<?php

class Mixtape_Rest_Api_Controller_CRUDTest extends MixtapeModelTestCase {
    /**
     * @var array
     */
    private $casettes;
    /**
     * @var WP_REST_Server
     */
    private $rest_server;

    function setUp() {
        parent::setUp();
        $env = $this->mixtape->environment();
        $env->define_model( new Casette() );
        /** @var WP_REST_Server $wp_rest_server */
        global $wp_rest_server;
        $this->rest_server = $wp_rest_server = new WP_REST_Server;
    }

    function test_exists() {
        $this->assertClassExists( 'Mixtape_Rest_Api_Controller_CRUD' );
    }

    function test_get_items_return_all_items() {
        $this->add_casette_rest_api_endpoint();
        $request = new WP_REST_Request( 'GET', '/casette-crud-test/v1/casettes' );
        $response = $this->rest_server->dispatch( $request );

        $this->assertNotNull( $response );
        $this->assertEquals( 200, $response->get_status() );
        $data = $response->get_data();
        $this->assertEquals( 2, count( $data ) );
        $this->assertEquals( 1, $data[0]['id'] );
        $this->assertEquals( 2, $data[1]['id'] );
    }

    function test_get_item_return_item() {
        $this->add_casette_rest_api_endpoint();
        $request = new WP_REST_Request( 'GET', '/casette-crud-test/v1/casettes/1' );
        $response = $this->rest_server->dispatch( $request );

        $this->assertNotNull( $response );
        $this->assertEquals( 200, $response->get_status() );
        $data = $response->get_data();
        $this->assertTrue( isset($data['id'] ) );
        $this->assertEquals( 1, $data['id'] );
    }

    function test_create_item_succeeds_when_data_store_returns_id() {
        $this->add_casette_rest_api_endpoint();
        $request = new WP_REST_Request( 'POST', '/casette-crud-test/v1/casettes' );
        $request->set_param( 'title', 'Awesome Mixtape 3');
        $request->set_param( 'songs', array( 1,2,3,4 ) );

        $response = $this->rest_server->dispatch( $request );

        $this->assertNotNull( $response );
        $this->assertEquals( 201, $response->get_status() );
        $data = $response->get_data();
        $this->assertTrue( isset($data['id'] ) );
        $this->assertEquals( 3, $data['id'] );
    }

    function test_update_item_succeeds_when_data_store_returns_id() {
        $this->add_casette_rest_api_endpoint();
        $request = new WP_REST_Request( 'PUT', '/casette-crud-test/v1/casettes/1' );
        $request->set_param( 'title', 'Awesome Mixtape 666');

        $response = $this->rest_server->dispatch( $request );

        $this->assertNotNull( $response );
        $this->assertEquals( 201, $response->get_status() );
        $data = $response->get_data();
        $this->assertTrue( isset($data['id'] ) );
        $this->assertEquals( 3, $data['id'] );
    }

    private function add_casette_rest_api_endpoint() {
        $env = $this->mixtape->environment();

        $mock_data_store = $this->build_mock_casette_data_store();
        $model_definition = $env
            ->model_definition( Casette::class )
            ->set_data_store( $mock_data_store );

        $bundle = $env
            ->define_bundle( 'casette-crud-test/v1' )
            ->add_endpoint( $env->crud( $model_definition, '/casettes' ) )
            ->build();
        $env->add_rest_bundle( $bundle )->start();
        // start all the things
        do_action( 'rest_api_init' );
    }

    function build_mock_casette_data_store() {
        $def = $this->environment->define_model( new Casette() )->model_definition( 'Casette' );
        $this->casettes = array();
        $this->casettes[] = $def->create_instance(array(
            'id' => 1,
            'title' => 'Awesome Mix Vol ' . 1,
            'songs' => array( 1, 2, 3 )
        ));
        $this->casettes[] = $def->create_instance(array(
            'id' => 2,
            'title' => 'Awesome Mix Vol ' . 2,
            'songs' => array( 1, 2, 3, 4 )
        ));
        $mock = $this->getMockBuilder(Mixtape_Interfaces_Data_Store::class)
            ->setMethods( get_class_methods(Mixtape_Interfaces_Data_Store::class) )
            ->getMock();
        $mock->expects($this->any())
            ->method('get_entities')
            ->willReturn( new Mixtape_Model_Collection( $this->casettes ) );
        $mock->expects($this->any())
            ->method('get_entity')
            ->willReturn( $this->casettes[0] );
        $mock->expects($this->any())
            ->method('upsert')
            ->willReturn( 3 );
        return $mock;
    }
}
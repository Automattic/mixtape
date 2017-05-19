<?php

class Mixtape_Data_Store_CustomPostTypeTest extends Mixtape_Testing_Model_TestCase {

    /**
     * @var Mixtape_Model_Definition
     */
    private $model_definition;

    function setUp() {
        parent::setUp();
        $env = $this->environment;
        $env->define()->model(
            'Casette'
        )->with_data_store(
            $env->define()->data_store()
            ->custom_post_type()
            ->with_post_type( 'mixtape_casette' )
        );
        $this->model_definition = $this->environment
            ->get()->model( Casette::class );
    }

    function test_upsert_inserts_new_entity() {
        $casette = $this->model_definition->create_instance(
            array(
                'title' => 'Awesome Mix Vol 1',
                'songs' => array( 1, 2, 3 )
            )
        );
        $data_store = $this->model_definition->get_data_store();
        $id_or_error = $data_store->upsert( $casette );
        $this->assertNotNull( $id_or_error, 'result is not null' );
        $this->assertFalse( is_wp_error( $id_or_error, 'result should not be a WP_Error' ) );
        $this->assertInternalType( 'int', $id_or_error, 'when successful, the new post id is returned' );
        $model = $data_store->get_entity( $id_or_error );
        $this->assertNotNull( $model, 'can get model by id' );
        $this->assertEquals( $id_or_error, $model->get_id(), 'model id should equal the id we got back on insert' );
        $this->assertEquals( array( 1, 2, 3 ), $model->get( 'songs' ) );
        $this->assertEquals( 'Awesome Mix Vol 1', $model->get( 'title' ) );
    }

    function test_upsert_updates_existing_entity() {
        $id_or_error = $this->insert_cassete();

        $model = $this->model_definition
            ->get_data_store()
            ->get_entity( $id_or_error );

        $this->assertEquals( array( 1, 2, 3 ), $model->get( 'songs' ) );
        $model->set( 'songs', array( 1 ) );
        $update_id_or_error = $this->model_definition
            ->get_data_store()
            ->upsert( $model );
        $this->assertNotNull( $update_id_or_error, 'result is not null' );
        $this->assertFalse( is_wp_error( $update_id_or_error, 'result should not be a WP_Error' ) );
        $this->assertInternalType( 'int', $update_id_or_error, 'when successful, the new post id is returned' );

        $model = $this->model_definition
            ->get_data_store()
            ->get_entity( $id_or_error );

        $this->assertNotNull( $model, 'can get model by id' );
        $this->assertEquals( $id_or_error, $model->get_id(), 'model id should equal the id we got back on insert' );

        $this->assertEquals( array( 1 ), $model->get( 'songs' ) );
    }

    private function insert_cassete() {
        $casette = $this->model_definition->create_instance(
            array(
                'title' => 'Awesome Mix Vol 1',
                'songs' => array( 1, 2, 3 )
            )
        );
        $data_store = $this->model_definition->get_data_store();
        $id_or_error = $data_store->upsert( $casette );

        return $id_or_error;
    }
}
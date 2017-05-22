<?php

class MT_Model_DefinitionTest extends MT_Testing_Model_TestCase {

    function test_exists() {
        $this->assertClassExists( 'MT_Model_Definition' );
    }

    function test_add_model_definition() {
        $env = $this->mixtape->load()
            ->environment();
        $env->define()->model( 'Casette' );
        $model_definition = $env->get()->model( Casette::class );
        $this->assertInstanceOf( MT_Model_Definition::class, $model_definition );
        $this->assertEquals( $model_definition->get_model_class(), Casette::class );
        $this->assertInstanceOf( MT_Data_Store_Nil::class, $model_definition->get_data_store() );
    }

    function test_get_field_declarations() {
         $this->mixtape
            ->environment()
            ->define()
             ->model( new Casette());
        $model_definition= $this->mixtape->environment()
            ->get()
            ->model( Casette::class );
        $declarations = $model_definition->get_field_declarations();
        $this->assertInternalType( 'array', $declarations );
        $this->assertEquals( 6, count( $declarations ) );
    }

    function test_create_with_array() {
         $this->mixtape
            ->environment()
            ->define()->model( new Casette() );
        $casette = $this->mixtape
            ->environment()->get()->model( Casette::class )
            ->create_instance( array(
                'title' => 'Awesome',
                'songs' => array(1,2,3)
            ) );

        $this->assertNotNull( $casette );
        $this->assertInstanceOf( MT_Model::class, $casette );
    }

    function test_find_one_by_id_from_cpt_entity_return_null_when_id_not_in_db() {
        $env = $this->mixtape
            ->environment();
        $casette_definition = $this->get_casette_definition();

        $model = $casette_definition->get_data_store()->get_entity( -1 );
        $this->assertNull( $model );
    }

    function test_find_one_by_id_from_cpt_entity_return_model() {
        $env = $this->mixtape
            ->environment();
        $casette_definition = $this->get_casette_definition();

        $casette_to_insert = $casette_definition->create_instance( array(
            'title' => 'Awesomeness',
            'author' => get_current_user_id(),
            'status' => 'publish',
            'songs'  => array( 1, 2, 3 )
        ) );

        $id = $casette_definition->get_data_store()->upsert( $casette_to_insert );
        if ( is_wp_error( $id ) ) {
            var_dump($id);
        }
        $this->assertFalse( is_wp_error( $id ) );
        $model = $casette_definition->get_data_store()->get_entity( $id );
        $this->assertNotNull( $model );
        $this->assertInstanceOf( MT_Model::class, $model );
        $model_id = $model->get( 'id' );
        $this->assertEquals( $id, $model_id );
        $model_meta_field = $model->get( 'songs' );
        $this->assertEquals( array( 1,2,3 ), $model_meta_field );
    }

    private function get_casette_definition() {
        $env = $this->mixtape
            ->environment();
         $env->define()
             ->model( 'Casette' )
             ->with_data_store(
                 $env->define()
                     ->data_store()
                     ->custom_post_type()
                     ->with_post_type( 'mixtape_cassette' )
             );
        return $env->get()->model( Casette::class );
    }
}
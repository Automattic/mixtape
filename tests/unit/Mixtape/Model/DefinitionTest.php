<?php

class Mixtape_Model_DefinitionTest extends MixtapeModelTestCase {
    function setUp() {
        parent::setUp();
    }

    function test_exists() {
        $this->assertClassExists( 'Mixtape_Model_Definition' );
    }

    function test_add_model_definition() {
        $env = $this->mixtape->load()
            ->environment();
        $env->define_model( new Casette(), new Mixtape_Data_Store_Nil() );
        $model_definition = $env->model( Casette::class );
        $this->assertInstanceOf( Mixtape_Model_Definition::class, $model_definition );
        $this->assertEquals( $model_definition->get_model_class(), Casette::class );
        $this->assertInstanceOf( Mixtape_Data_Store_Nil::class, $model_definition->get_data_store() );
    }

    function test_get_field_declarations() {
        $model_definition = $this->mixtape
            ->environment()
            ->define_model( new Casette(), new Mixtape_Data_Store_Nil() )
            ->model( Casette::class );
        $declarations = $model_definition->get_field_declarations();
        $this->assertInternalType( 'array', $declarations );
        $this->assertEquals( 4, count( $declarations ) );
    }

    function test_create_with_array() {
        $casette = $this->mixtape
            ->environment()
            ->define_model( new Casette(), new Mixtape_Data_Store_Nil() )
            ->model( Casette::class )
            ->create_instance( array( 'title' => 'Awesome', 'songs' => array(1,2,3) ) );

        $this->assertNotNull( $casette );
        $this->assertInstanceOf( Mixtape_Model::class, $casette );
    }
}
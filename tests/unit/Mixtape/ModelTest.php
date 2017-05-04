<?php

class Mixtape_ModelTest extends MixtapeModelTestCase {
    function setUp() {
        parent::setUp();

    }
    function test_exists() {
        $this->assertClassExists( 'Mixtape_Model' );
    }


    function test_constructor_with_array() {
//        $data_store = $this->getMockBuilder()
//        $casette_factory = $this->environment->get_factory(Casette::class);
//        $casette_id = $this->create_a_casette( 1 );
//        $def = $this
//            ->environment
//            ->define_model( Casette::class )
//            ->set_data_store( new Mixtape_Data_Store_Nil() );
//        $this
//            ->environment
//            ->add_model_definition( $def );
//        $casette = new Casette( array('ID' => 1, 'title' => 'Awesome Mix Vol 1') );
//        $this->assertEquals( $casette->get_value_for( 'id' ), 1 );
    }

    function create_a_casette( $vol ) {
        $args = array(
            'post_content' => 'Awesome Mix Vol ' . $vol,
            'post_name' => 'awesome-mix-vol-' . $vol ,
            'post_title' => 'Awesome Mix Vol ' . $vol,
            'post_status' => 'publish',
            'post_type' => 'mixtape_casette'
        );

        return wp_insert_post( $args );
    }
}
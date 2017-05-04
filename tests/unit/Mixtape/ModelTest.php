<?php

class Mixtape_ModelTest extends MixtapeModelTestCase {
    function setUp() {
        parent::setUp();

    }
    function test_exists() {
        $this->assertClassExists( 'Mixtape_Model' );
    }

    function test_get() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( $tape->get( 'id' ), 1 );
        $this->assertEquals( $tape->get( 'title' ), 'Awesome Mix Vol 1' );
    }

    function test_get_with_meta_fields() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( $tape->get( 'songs' ), array( 1, 2, 3 ) );
    }

    function test_with_derived_fields() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( $tape->get( 'ratings' ), array( 1 ) );
    }

    function create_awesome_mix( $vol ) {
        return $this->create_casette( array(
            'id' => $vol,
            'title' => 'Awesome Mix Vol ' . $vol,
            'songs' => array( 1, 2, 3 )
        ) );
    }

    function create_casette( $props ) {
        return $this->mixtape->environment()
            ->define_model( new Casette(), new Mixtape_Data_Store_Nil() )
            ->model( Casette::class )
            ->create_instance( $props );
    }
}
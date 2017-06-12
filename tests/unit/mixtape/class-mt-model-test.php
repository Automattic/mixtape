<?php

class MT_ModelTest extends MT_Testing_Model_TestCase {
    function setUp() {
        parent::setUp();

    }

    function test_exists() {
        $this->assertClassExists( 'MT_Model' );
    }

    function test_get() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( 1, $tape->get( 'id' ) );
        $this->assertEquals( 'Awesome Mix Vol 1', $tape->get( 'title' ) );
    }

    function test_get_with_meta_fields() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( array( 1, 2, 3 ), $tape->get( 'songs' ) );
    }

    function test_with_derived_fields() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( array( 1 ), $tape->get( 'ratings' ) );
    }

    /**
     * @expectedException MT_Exception
     */
    function test_set_throws_if_field_unknown() {
        $tape = $this->create_awesome_mix( 1 );
        $tape->set( 'foobar', 1 );
    }

    /**
     * @expectedException MT_Exception
     */
    function test_get_throws_if_field_unknown() {
        $tape = $this->create_awesome_mix( 1 );
        $tape->get( 'foobar' );
    }

    function create_awesome_mix( $vol ) {
        return $this->create_casette( array(
            'id' => $vol,
            'title' => 'Awesome Mix Vol ' . $vol,
            'songs' => array( 1, 2, 3 )
        ) );
    }

    function create_casette( $props ) {
        $this->mixtape->environment()
            ->define_model( 'Casette' );
        return $this->mixtape->environment()->model( 'Casette' )
            ->create_instance( $props );
    }
}
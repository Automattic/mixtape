<?php
/**
 * Model tests
 *
 * @package Mixtape/Tests
 */

/**
 * Class MT_Type_ModelTest
 */
class MT_EventsTest extends MT_Testing_TestCase {

    private $sum_value = 0;
    private $sum_value_2 = 0;

    function setUp() {
        parent::setUp();
        $this->sum_value = 0;
    }

    function test_exists() {
        $this->assertClassExists( 'MT_Events' );
    }

    function test_do_action_does_action() {
        $dispatch = new MT_Events();
        $dispatch->add_action( 'test_dispatch', array( $this, 'mutating_action_with_args' ), 10, 2 );
        $dispatch->do_action( 'test_dispatch', 1 , 3 );
        $this->assertSame( $this->sum_value, 4 );
    }

    function test_do_action_does_action_and_is_isolated() {
        $dispatch = new MT_Events();
        $dispatch->add_action( 'test_dispatch', array( $this, 'mutating_action_with_args' ), 10, 2 );
        $dispatch_two = new MT_Events();
        $dispatch_two->add_action( 'test_dispatch', array( $this, 'mutating_action_with_args' ), 10, 2 );
        $dispatch->do_action( 'test_dispatch', 1 , 3 );
        $this->assertSame( $this->sum_value, 4 );
    }

    function test_do_action_does_action_and_accepts_multiple_priorities() {
        $dispatch = new MT_Events();
        $dispatch->add_action( 'test_dispatch', array( $this, 'mutating_action_with_args' ), 10, 2 );

        $dispatch->add_action( 'test_dispatch', array( $this, 'mutating_action_with_args' ), 9, 2 );
        $dispatch->do_action( 'test_dispatch', 1 , 3 );
        $this->assertSame( $this->sum_value, 8 );
    }

    function test_apply_filters() {
        $dispatch = new MT_Events();
        $dispatch->add_filter( 'test_filter', array( $this, 'filter_with_args' ), 10, 2 );
        $result = $dispatch->apply_filters( 'test_filter', 1, 3 );
        $this->assertSame( 4, $result );
        $dispatch->add_filter( 'test_filter', array( $this, 'filter_with_args_two' ), 10, 2 );
        $result = $dispatch->apply_filters( 'test_filter', 1, 3 );
        $this->assertSame( 7, $result );

    }

    function mutating_action_with_args( $first, $second ) {
        $this->sum_value = $this->sum_value + $first + $second;
    }

    function filter_with_args_two( $first, $second ) {
        return $first + $second;
    }

    function filter_with_args( $first, $second ) {
        return $first + $second;
    }
}
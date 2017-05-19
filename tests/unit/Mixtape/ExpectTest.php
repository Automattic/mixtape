<?php

class Mixtape_ExpectTest extends Mixtape_Testing_TestCase {

    private $mixtape;

    function test_exists() {
        $this->assertClassExists( 'Mixtape_Bootstrap' );
    }

    /**
     * @expectedException Mixtape_Exception
     */
    function test_that_throws_if_false() {
        $this->mixtape = Mixtape_Bootstrap::create()->load();
        Mixtape_Expect::that( false, 'fails' );
    }

    /**
     * @expectedException Mixtape_Exception
     */
    function test_is_a_throws_if_not_class() {
        $this->mixtape = Mixtape_Bootstrap::create()->load();
        Mixtape_Expect::is_a( new stdClass(), 'Mixtape_Environment' );
    }
}
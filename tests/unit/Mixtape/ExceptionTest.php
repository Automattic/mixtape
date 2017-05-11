<?php

class Mixtape_ExceptionTest extends Mixtape_Testing_TestCase {

    function test_exists() {
        Mixtape_Bootstrap::create()->load();
        $this->assertClassExists( 'Mixtape_Exception' );
    }
}
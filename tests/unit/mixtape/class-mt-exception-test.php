<?php

class MT_ExceptionTest extends MT_Testing_TestCase {

    function test_exists() {
        MT_Bootstrap::create()->load();
        $this->assertClassExists( 'MT_Exception' );
    }
}
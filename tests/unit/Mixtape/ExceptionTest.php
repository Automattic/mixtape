<?php

class Mixtape_ExceptionTest extends MixtapeTestCase {
    private $mixtape;

    function test_exists() {
        $this->mixtape = Mixtape::create()->load();
        $this->assertClassExists( 'Mixtape_Exception' );
    }
}
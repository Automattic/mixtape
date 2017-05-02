<?php

class Mixtape_EnvironmentTest extends MixtapeTestCase {
    private $mixtape;

    function test_exists() {
        $this->mixtape = Mixtape::create()->load();
        $this->assertClassExists( 'Mixtape_Environment' );
        $env = $this->mixtape->environment();
        $this->assertInstanceOf( $env, 'Mixtape_Environment' );
    }

    function test() {

    }
}
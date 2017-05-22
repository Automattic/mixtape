<?php

class MT_BootstrapTest extends MT_Testing_TestCase {

    private $mixtape;

    function test_exists() {
        $this->assertClassExists( 'MT_Bootstrap' );
    }

    function test_class_loader_return_class_loader() {
        $this->mixtape = MT_Bootstrap::create();
        $loader = $this->mixtape->class_loader();
        $this->assertNotNull( $loader );
        $this->assertInstanceOf( MT_Interfaces_Classloader::class, $loader );
    }

    function test_load_loads_classes() {
        $this->mixtape = MT_Bootstrap::create()->load();
        $this->assertClassExists( MT_Environment::class );
    }
}

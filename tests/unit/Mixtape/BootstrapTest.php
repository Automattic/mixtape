<?php

class Mixtape_BootstrapTest extends Mixtape_Testing_TestCase {

    private $mixtape;

    function test_exists() {
        $this->assertClassExists( 'Mixtape_Bootstrap' );
    }

    function test_class_loader_return_class_loader() {
        $this->mixtape = Mixtape_Bootstrap::create();
        $loader = $this->mixtape->class_loader();
        $this->assertNotNull( $loader );
        $this->assertInstanceOf( 'Mixtape_Interfaces_Class_Loader', $loader );
    }

    function test_load_loads_classes() {
        $this->mixtape = Mixtape_Bootstrap::create()->load();
        $this->assertClassExists( 'Mixtape_Environment' );
        //////
    }
}

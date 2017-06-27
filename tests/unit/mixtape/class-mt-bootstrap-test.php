<?php

/**
 * Class MT_BootstrapTest
 */
class MT_BootstrapTest extends MT_Testing_TestCase {

    function test_exists() {
        $this->assertClassExists( 'MT_Bootstrap' );
    }

    function test_class_loader_return_class_loader() {
		$bootstrap = MT_Bootstrap::create();
        $loader = $bootstrap->class_loader();
        $this->assertNotNull( $loader );
        $this->assertInstanceOf( 'MT_Interfaces_Classloader', $loader );
    }

    function test_load_loads_classes() {
        MT_Bootstrap::create()->load();
        $this->assertClassExists( 'MT_Environment' );
    }

	function test_environment_is_singleton() {
		$bootstrap = MT_Bootstrap::create()->load();
		$this->assertSame( $bootstrap->environment(), $bootstrap->environment() );
	}
}

<?php

class Mixtape_EnvironmentTest extends Mixtape_Testing_TestCase {
    /**
     * @var Mixtape_Bootstrap
     */
    private $bootstrap;

    function setUp() {
        parent::setUp();
        $this->bootstrap = Mixtape_Bootstrap::create()->load();
    }

    function test_exists() {
        $this->assertClassExists( 'Mixtape_Environment' );
        $env = $this->bootstrap->environment();
        $this->assertNotNull( $env );
        $this->assertInstanceOf( 'Mixtape_Environment', $env );
    }

    function test_start_calls_start_in_added_bundles() {
        $a_bundle = $this
            ->getMockBuilder( Mixtape_Interfaces_Rest_Api_Controller_Bundle::class )
            ->setMethods( array( 'get_bundle_prefix', 'start', 'register', 'get_endpoints' ) )
            ->getMock();

        $a_bundle->expects($this->once())
            ->method('get_bundle_prefix')
            ->willReturn('/foo/v1');
        $a_bundle->expects($this->once())
            ->method('start');

        $this->bootstrap
            ->environment()
            ->define()->rest_api( $a_bundle );
        $this->bootstrap->environment()->start();
    }

    function test_full_class_name() {
        $class_name = $this->bootstrap->environment()->full_class_name('Environment');
        $this->assertEquals( 'Mixtape_Environment', $class_name );
    }

    function test_endpoint_returns_builder() {
        $b = $this->bootstrap->environment()->define()->rest_api('zzz')->endpoint();
        $this->assertInstanceOf( 'Mixtape_Rest_Api_Controller_Builder', $b );
    }

    function test_crud_returns_builder() {
        $b = $this->bootstrap->environment()->define()->rest_api('zzz')->endpoint()->crud();
        $this->assertInstanceOf( 'Mixtape_Rest_Api_Controller_Builder', $b );
    }
}
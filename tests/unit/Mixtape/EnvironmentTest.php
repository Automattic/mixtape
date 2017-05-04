<?php

class Mixtape_EnvironmentTest extends MixtapeTestCase {
    /**
     * @var Mixtape
     */
    private $mixtape;

    function setUp() {
        parent::setUp();
        $this->mixtape = Mixtape::create()->load();
    }

    function test_exists() {
        $this->assertClassExists( 'Mixtape_Environment' );
        $env = $this->mixtape->environment();
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

        $this->mixtape
            ->environment()
            ->add_rest_bundle( $a_bundle );
        $this->mixtape->environment()->start();
    }
}
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

    /**
     * @covers Mixtape_Environment::start
     */
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

    function test_endpoint_returns_builder() {
        $b = $this->bootstrap->environment()->define()->rest_api('zzz')->endpoint();
        $this->assertInstanceOf( 'Mixtape_Rest_Api_Controller_Builder', $b );
    }

    function test_crud_returns_builder() {
        $b = $this->bootstrap->environment()->define()->rest_api('zzz')->endpoint()->crud();
        $this->assertInstanceOf( 'Mixtape_Rest_Api_Controller_Builder', $b );
    }

    /**
     * @covers Mixtape_Environment::define
     */
    function test_define_return_fluid_define() {
        $define = $this->bootstrap->environment()->define();
        $this->assertInstanceOf( Mixtape_FluentInterface_Define::class, $define );
    }

    /**
     * @covers Mixtape_Environment::get
     */
    function test_get_return_fluid_get() {
        $define = $this->bootstrap->environment()->get();
        $this->assertInstanceOf( Mixtape_FluentInterface_Get::class, $define );
    }

    /**
     * @expectedException Mixtape_Exception
     * @covers Mixtape_Environment::push_builder
     */
    function test_push_builder_throws_when_no_valid_class() {
        $this->bootstrap->environment()->push_builder( 'models', new stdClass());
    }

    /**
     * @expectedException Mixtape_Exception
     * @covers Mixtape_Environment::model_definition
     */
    function test_model_definition_throw_if_unknown_class() {
        $this->bootstrap->environment()->model_definition( 'Foo' );
        ///
    }

    /**
     * @expectedException Mixtape_Exception
     * @covers Mixtape_Environment::model_definition
     */
    function test_model_definition_throw_if_no_definition() {
        $this->bootstrap->environment()->model_definition( Mixtape_Model_Declaration_Settings::class );
    }

    /**
     * @covers Mixtape_Environment::model_definition
     */
    function test_model_definition_return_definition() {
        $this->bootstrap->environment()->define()->model( Mixtape_Model_Declaration_Settings::class );
        $d = $this->bootstrap->environment()->model_definition( Mixtape_Model_Declaration_Settings::class );
        $this->assertInstanceOf( Mixtape_Model_Definition::class, $d );
    }
}
<?php

class MT_EnvironmentTest extends MT_Testing_TestCase {
    /**
     * @var MT_Bootstrap
     */
    private $bootstrap;

    function setUp() {
        parent::setUp();
        $this->bootstrap = MT_Bootstrap::create()->load();
    }

    function test_exists() {
        $this->assertClassExists( 'MT_Environment' );
        $env = $this->bootstrap->environment();
        $this->assertNotNull( $env );
        $this->assertInstanceOf( 'MT_Environment', $env );
    }

    /**
     * @covers Mixtape_Environment::start
     */
    function test_start_calls_register_in_added_bundles() {
		$this->requires_php_53_or_greater();
        $a_bundle = $this
            ->getMockBuilder( 'MT_Interfaces_Controller_Bundle' )
            ->setMethods( array( 'get_prefix', 'start', 'register', 'get_endpoints' ) )
            ->getMock();

        $a_bundle->expects($this->once())
            ->method('get_prefix')
            ->willReturn('/foo/v1');
        $a_bundle->expects($this->once())
            ->method('register');

        $this->bootstrap
            ->environment()
			->rest_api( $a_bundle );
        $this->bootstrap->environment()->start();
    }

    function test_add_endpoint_returns_builder() {
        $b = $this->bootstrap->environment()->rest_api('zzz')->add_endpoint( new MT_Controller_CRUD('/foo', 'FooBar' ));
        $this->assertInstanceOf( 'MT_Controller_Bundle_Builder', $b );
    }

    /**
     * @expectedException MT_Exception
     * @covers Mixtape_Environment::push_builder
     */
    function test_push_builder_throws_when_no_valid_class() {
        $this->bootstrap->environment()->push_builder( MT_Environment::MODELS, new stdClass());
    }

    /**
     * @expectedException MT_Exception
     * @covers Mixtape_Environment::model_definition
     */
    function test_model_definition_throw_if_unknown_class() {
        $this->bootstrap->environment()->model( 'Foo' );
    }

    /**
     * @expectedException MT_Exception
     * @covers Mixtape_Environment::model_definition
     */
    function test_model_definition_throw_if_no_definition() {
        $this->bootstrap->environment()->model( 'MT_Model_Settings' );
    }

	function test_define_var_defines_var() {
		$env = $this->bootstrap->environment();
		$env->define_var( 'foo', 'bar' );
		$this->assertSame( 'bar', $env->get( 'foo' ) );
	}

	function test_add_var_appends_to_list() {
		$env = $this->bootstrap->environment();
		$env->array_var( 'listfoo', 'bar' );
		$lst = $env->get( 'listfoo' );
		$this->assertInternalType( 'array', $lst );
		$this->assertSame( 'bar', $lst[0] );
	}
}
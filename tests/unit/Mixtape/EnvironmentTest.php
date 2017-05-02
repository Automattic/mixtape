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

    function test_set_data_store() {
        $data_store = $this
            ->getMockBuilder(Mixtape_Interfaces_Data_Store::class)
            ->getMock();
        $data_store_key = 'Foo';
        $this->mixtape
            ->environment()
            ->set_data_store( $data_store_key, $data_store);
        $ref = $this->mixtape
            ->environment()
            ->get_data_store( $data_store_key );
        $this->assertNotNull( $ref );
        $this->assertInstanceOf( Mixtape_Interfaces_Data_Store::class, $ref );
        $this->assertSame( $data_store, $ref );
    }

    /**
     * @expectedException Mixtape_Exception
     */
    function test_get_data_store_throws_if_key_not_exists() {
        $this->mixtape->environment()->get_data_store('Bar');
    }

    function test_add_bundle_adds_bundle() {
        $a_bundle = $this
            ->getMockBuilder(Mixtape_Interfaces_Interfaces_Rest_Api_Controller_Bundle::class)
            ->setMethods( array( 'get_bundle_prefix', 'start' ) )
            ->getMock();
        // Configure the stub.
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
<?php

class Mixtape_MixtapeTest extends MixtapeTestCase {

    private $mixtape;

    function test_exists() {
        $this->assertClassExists( 'Mixtape' );
    }

    function test_class_loader_return_class_loader() {
        $this->mixtape = Mixtape::create();
        $loader = $this->mixtape->class_loader();
        $this->assertNotNull( $loader );
        $this->assertInstanceOf( 'Mixtape_Interfaces_Class_Loader', $loader );
    }

    function test_load_loads_classes() {
        $this->mixtape = Mixtape::create()->load();
        $this->assertClassExists( 'Mixtape_Environment' );
    }

    function test_load_with_custom_prefix_loads_classes() {
        $this->mixtape = Mixtape::create( array( 'prefix' => 'Test_Mixtape' ) );
        $this->mixtape
            ->class_loader()
            ->load_class('Environment');
        $this->assertClassExists( 'Test_Mixtape_Environment' );
    }

    function test_different_prefixes_have_no_conflicts() {
        $mixtape_vol_one = Mixtape::create( array( 'prefix' => 'Test_Mixtape' ) );
        $mixtape_vol_two = Mixtape::create( array( 'prefix' => 'Test_Mixtape_Two' ) );
        $mixtape_vol_one->class_loader()->load_class('Environment');
        $mixtape_vol_two->class_loader()->load_class('Environment');
        $this->assertClassExists( 'Test_Mixtape_Environment' );
        $this->assertClassExists( 'Test_Mixtape_Two_Environment' );
    }
}
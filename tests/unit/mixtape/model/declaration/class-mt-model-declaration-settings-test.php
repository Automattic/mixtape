<?php

class MT_Model_SettingsTest extends MT_Testing_Model_TestCase {
    function test_exists() {
        $this->assertClassExists( 'MT_Model_Settings' );
    }

    function test_field_types() {
        $setting = $this->get_settings();
        $this->assertNotNull( $setting );
        $this->assertFalse( $setting->get('mixtape_casette_hide_listened') );
        $this->assertTrue( $setting->get('mixtape_casette_enable_private') );
        $this->assertEquals( 10, $setting->get( 'mixtape_casette_per_page' ) );
    }

    function test_dto_field_names() {
        $setting = $this->get_settings();
        $this->assertNotNull( $setting );
        $def = $this->environment->model( 'CasetteSettings' );
        $dto = $setting->to_dto();
        $this->assertNotNull( $dto );
        $this->assertArrayHasKey('hide_listened', $dto );
        $this->assertArrayHasKey('enable_private', $dto );
        $this->assertArrayHasKey('per_page', $dto );
    }

    private function get_settings() {
        $env = $this->mixtape->load()
            ->environment();
        $env->define_model( 'CasetteSettings' );
        return $env->model( 'CasetteSettings' )
            ->create(
            array(
                'mixtape_casette_hide_listened'  => false,
                'mixtape_casette_per_page'       => 10,
                'mixtape_casette_enable_private' => true,
            )
        );
    }
}
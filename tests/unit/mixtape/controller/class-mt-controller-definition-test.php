<?php
/**
 * MT_Controller_DefinitionTest
 *
 * @package Mixtape/Tests
 */

class MT_Controller_DefinitionTest extends MT_Testing_Controller_TestCase {
    function test_class_is_loaded() {
        $this->assertClassExists( 'MT_Controller_Definition' );
    }
}
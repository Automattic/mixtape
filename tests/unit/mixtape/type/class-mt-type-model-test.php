<?php
/**
 * Model tests
 *
 * @package Mixtape/Tests
 */

/**
 * Class MT_Type_ModelTest
 */
class MT_Type_ModelTest extends MT_Testing_TestCase {

    /**
     * Registry.
     *
     * @var MT_Type_Registry
     */
    private $type_registry;
    /**
     * Type.
     *
     * @var MT_Type_Array
     */
    private $array_type;

    function setUp() {
        parent::setUp();
        $this->type_registry = $this->environment->get_type_registry();
    }

    function test_exists() {
        $this->assertClassExists( 'MT_Type_Model' );
    }

    function test_definition_return_model_type() {
        $typedef = $this->type_registry->definition( 'model:MT_Model' );
        $this->assertNotNull( $typedef );
        $this->assertTrue( is_a( $typedef, 'MT_Type_Model' ) );
    }
}
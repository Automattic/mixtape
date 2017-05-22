<?php

class MT_Type_RegistryTest extends MT_Testing_Model_TestCase {

    /**
     * @var MT_Type_Registry
     */
    private $type_registry;

    function setUp() {
        parent::setUp();
        $this->type_registry = $this->environment->type();
    }

    function test_exists() {
        $this->assertClassExists( 'MT_Type_Registry' );
    }

    /**
     * @expectedException MT_Exception
     */
    function test_define_type_throws_when_not_Mixtape_Interfaces_Type() {
        $this->type_registry->define('foo', 'bar');
    }

    /**
     * @expectedException MT_Exception
     */
    function test_get_type_definition_throws_when_no_definition_exists() {
        $this->type_registry->definition('mycustomtype');
    }

    function test_get_type_definition_return_type() {
        $str_type = $this->type_registry->definition( 'string' );
        $this->assertInstanceOf( MT_Interfaces_Type::class, $str_type );
    }

    function test_define_type_create_new_type() {
        $uint = $this->type_registry->definition( 'uint' );
        $this->type_registry->define('uint32', $uint );
        $type = $this->type_registry->definition( 'uint32' );
        $this->assertInstanceOf( MT_Interfaces_Type::class, $type );
        $this->assertSame( $uint, $type );
    }

    function test_get_type_definition_create_new_container_type_lazily() {
        $uint = $this->type_registry->definition( 'uint' );
        $this->type_registry->define( 'uint32', $uint );
        $str_type = $this->type_registry->definition( 'array:uint32' );
        $this->assertInstanceOf( MT_Interfaces_Type::class, $str_type );
    }

    /**
     * @expectedException MT_Exception
     */
    function test_get_type_definition_throw_if_compound_type_malformed() {
        $this->type_registry->definition( 'array:' );
    }

    /**
     * @expectedException MT_Exception
     */
    function test_get_type_definition_throw_if_compound_type_empty() {
        $this->type_registry->definition( ':uint' );
    }
}
<?php

class MT_Type_TypedArrayTest extends MT_Testing_TestCase {
    /**
     * @var MT_Type_Registry
     */
    private $type_registry;
    /**
     * @var MT_Type_TypedArray
     */
    private $typed_array;

    function setUp() {
        parent::setUp();
        $this->type_registry = $this->environment->get_type_registry();
        $this->typed_array = $this->type_registry->definition( 'array:integer' );
    }

    function test_exists() {
        $this->assertClassExists( 'MT_Type_TypedArray' );
    }

    function test_is_registered() {
        $this->assertInstanceOf( 'MT_Type_TypedArray', $this->typed_array );
    }

    function test_default_value() {
        $this->assertEquals( array(), $this->typed_array->default_value() );
    }

    function test_casts() {
        $this->assertEquals( array( 1 ), $this->typed_array->cast( array( '1' ) ) );
        $this->assertEquals( array( 0, 1, 0, 0, 3 ), $this->typed_array->cast( array( '', '1', false, null, 3.1 ) ) );
        $this->assertEquals( array( 0, 0, 0, 1 ), $this->typed_array->cast( array( 0.1, 0.2, 0.3, 1.1 ) ) );
    }

    /**
     * Test the schema
     *
     * @covers MT_Type_TypedArray::schema
     */
    function test_schema() {
        $schema = $this->typed_array->schema();
        $this->assertInternalType( 'array', $schema );
        $this->assertArrayHasKey( 'type', $schema );
        $this->assertEquals( 'array', $schema['type'] );
        $this->assertArrayHasKey( 'items', $schema );
        $this->assertInternalType( 'array', $schema['items'] );
        $this->assertArrayHasKey( 'type', $schema['items'] );
        $this->assertEquals( 'integer', $schema['items']['type'] );
    }
}
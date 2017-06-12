<?php
/**
 * Array tests
 *
 * @package Mixtape/Tests
 */

/**
 * Class MT_Type_ArrayTest
 */
class MT_Type_ArrayTest extends MT_Testing_Model_TestCase {

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
        $this->type_registry = $this->environment->type();
        $this->array_type = $this->type_registry->definition( 'array' );
    }

    function test_exists() {
        $this->assertClassExists( 'MT_Type_Array' );
    }

    function test_cast() {
        $this->assertEquals( array( 1, false ), $this->array_type->cast( array( 1, false ) ) );
    }

    function test_default() {
        $this->assertEquals( array(), $this->array_type->default_value() );
    }

    function test_integer_type() {
        $int_type = $this->type_registry->definition( 'integer' );
        $this->assertInstanceOf( 'MT_Type_Integer', $int_type );

        $this->assertEquals( 0, $int_type->default_value() );
        $this->assertEquals( 'integer', $int_type->name() );

        $this->assertSame(1, $int_type->cast( '1' ) );
        $this->assertEquals( 12, $int_type->sanitize( '12.1' ) );
    }

    function test_nullable() {
        $nullable_int = $this->type_registry->definition( 'nullable:integer' );
        $this->assertInstanceOf( 'MT_Type_Nullable', $nullable_int );

        $this->assertNull( $nullable_int->default_value() );
        $this->assertEquals( 'nullable:integer', $nullable_int->name() );

        $this->assertSame(1, $nullable_int->cast( '1' ) );
        $this->assertNull( $nullable_int->cast( null ) );
        $this->assertEquals( 12, $nullable_int->sanitize( '12.1' ) );
        $this->assertEquals( 0, $nullable_int->sanitize( '' ) );
    }

    function test_typed_array() {
        $array_of_ints = $this->type_registry->definition( 'array:integer' );
        $this->assertInstanceOf( 'MT_Type_TypedArray', $array_of_ints );

        $this->assertEquals( array(), $array_of_ints->default_value() );
        $this->assertEquals( 'array:integer', $array_of_ints->name() );

        $this->assertEquals( array( 1 ), $array_of_ints->cast( array( '1' ) ) );
        $this->assertEquals( array( 0, 0, 0, 1 ), $array_of_ints->cast( array( 0.1, 0.2, 0.3, 1.1 ) ) );
    }

    function test_boolean() {
        $bool_type = $this->type_registry->definition( 'boolean' );
        $this->assertInstanceOf( 'MT_Type_Boolean', $bool_type );

        $this->assertEquals( false, $bool_type->default_value() );
        $this->assertEquals( 'boolean', $bool_type->name() );

        $this->assertEquals( true, $bool_type->cast( '1' ) );
        $this->assertEquals( true, $bool_type->cast( 0.1 ) );
        $this->assertEquals( true, $bool_type->cast( array( '1' ) ) );
        $this->assertEquals( false, $bool_type->cast( array() ) );
        $this->assertEquals( false, $bool_type->cast( 'false' ), "makes more sense that 'false' is false" );
        $this->assertEquals( false, $bool_type->cast( 0 ) );
        $this->assertEquals( false, $bool_type->cast( 0.0 ) );
        $this->assertEquals( false, $bool_type->cast( null ) );
        $this->assertEquals( false, $bool_type->cast( '' ) );
    }
}
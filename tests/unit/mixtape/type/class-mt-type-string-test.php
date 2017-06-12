<?php
/**
 * Array tests
 *
 * @package Mixtape/Tests
 */

/**
 * Class MT_Type_ArrayTest
 */
class MT_Type_StringTest extends MT_Testing_Model_TestCase {
    /**
     * Registry.
     *
     * @var MT_Type_Registry
     */
    private $type_registry;
    /**
     * Type.
     *
     * @var MT_Type_String
     */
    private $string_type;

    function setUp() {
        parent::setUp();
        $this->type_registry = $this->environment->type();
        $this->string_type = $this->type_registry->definition( 'string' );
    }

    function test_string_type_exists() {
        $this->assertClassExists( 'MT_Type_String' );
        $this->assertInstanceOf( 'MT_Type_String', $this->string_type );
        $this->assertEquals( 'string', $this->string_type->name() );
    }

    function test_default() {
        $this->assertEquals( '', $this->string_type->default_value() );
    }

    function test_cast() {
        $this->assertSame( '1', $this->string_type->cast( 1 ) );
        $this->assertSame( '', $this->string_type->cast( null ) );
        $this->assertSame( '3.14', $this->string_type->cast( 3.14 ) );
        $this->assertSame( 'foo', $this->string_type->cast( 'foo' ) );
        $this->assertSame( '(1,2,3)', $this->string_type->cast( array( 1, 2, 3 ) ) );
        $this->assertSame( '(1,foo,(3))', $this->string_type->cast( array( 1, 'foo', array( 3 ) ) ) );
    }

    function test_sanitize() {
        $a_string = 'example string<html></html>';
        $this->assertEquals( 'example string', $this->string_type->sanitize( $a_string ) );
    }
}
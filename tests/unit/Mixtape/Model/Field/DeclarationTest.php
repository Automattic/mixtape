<?php

class Mixtape_Model_Field_DeclarationTest extends Mixtape_Testing_Model_TestCase {
    function test_exists() {
        $this->assertClassExists( 'Mixtape_Model_Field_Declaration' );
    }

    /**
     * @expectedException Mixtape_Exception
     */
    function test_construct_throws_if_name_not_provided() {
        new Mixtape_Model_Field_Declaration( array() );
    }

    /**
     * @expectedException Mixtape_Exception
     */
    function test_construct_throws_if_type_not_provided() {
        new Mixtape_Model_Field_Declaration( array('name' => 'foo') );
    }

    /**
     * @expectedException Mixtape_Exception
     */
    function test_construct_throws_if_type_not_valid() {
        new Mixtape_Model_Field_Declaration( array('name' => 'foo', 'type' => 'zap') );
    }

    function test_field_declarations() {

        $sum_declaration = (new Mixtape_Model_Field_DeclarationBuilder())
            ->with_field_type(Mixtape_Model_Field_Types::FIELD)
            ->named( 'sum' )
            ->of_type( Mixtape_Model_Field_Declaration::INT_VALUE )
            ->build();
        $this->assertTrue( $sum_declaration->is_field() );
        $this->assertEquals( $sum_declaration->get_default_value(), 0 );
        $this->assertEquals( $sum_declaration->get_map_from(), $sum_declaration->name );
        $this->assertSame( $sum_declaration->cast_value('0'), 0);
        $this->assertSame( $sum_declaration->cast_value( 0.1 ), 0);

        $first_name_declaration = (new Mixtape_Model_Field_DeclarationBuilder())
            ->with_field_type( Mixtape_Model_Field_Types::FIELD )
            ->named( 'first_name' )
            ->of_type( Mixtape_Model_Field_Declaration::STRING_VALUE )
            ->with_default( 'Foobar' )
            ->map_from( 'firstName' )
            ->required(true)
            ->build();
        $this->assertTrue( $first_name_declaration->is_field() );
        $this->assertTrue( $first_name_declaration->required );
        $this->assertEquals( 'Foobar', $first_name_declaration->get_default_value() );
        $this->assertNotEquals( $first_name_declaration->get_map_from(), $first_name_declaration->get_name() );
        $this->assertSame( '0', $first_name_declaration->cast_value(0) );

        $derived_declaration = (new Mixtape_Model_Field_DeclarationBuilder())
            ->with_field_type( Mixtape_Model_Field_Types::DERIVED )
            ->named( 'derived' )
            ->build();
        $this->assertTrue( $derived_declaration->is_derived_field() );
        $this->assertEquals( $derived_declaration->get_default_value(), null );
    }

    function test_get_description_default_to_name() {
        $field =(new Mixtape_Model_Field_DeclarationBuilder())
            ->with_field_type( Mixtape_Model_Field_Types::FIELD )
            ->named( 'first_name' )->build();
        $this->assertEquals( 'First name', $field->get_description() );
    }
    function test_as_item_schema_property_contain_choices_if_set() {
        $choices = array( 'a', 'b', 'c' );
        $builder = new Mixtape_Model_Field_DeclarationBuilder();
        $builder->with_field_type( Mixtape_Model_Field_Types::FIELD )
            ->choices( $choices )
            ->of_type('string')
            ->named('field_with_choices')->build();
        $field = $builder->build();
        $this->assertEquals( $choices, $field->get_choices() );
        $schema = $field->as_item_schema_property();
        $this->assertEquals( 'string', $schema['type'] );
        $this->assertTrue( isset( $schema['oneOf'] ) );
        $this->assertEquals( $choices, $schema['oneOf'] );
    }
}
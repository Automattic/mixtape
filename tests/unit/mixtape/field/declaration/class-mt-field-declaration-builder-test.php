<?php

class MT_Field_Declaration_BuilderTest extends MT_Testing_Model_TestCase {
	function test_exists() {
		$this->assertClassExists( 'MT_Field_Declaration_Builder' );
	}

	function test_build_field() {
		$func = array( $this, 'some_callable' );
		$env = $this->environment;
		$builder = new MT_Field_Declaration_Builder();
		$builder->with_name( 'example' )
			->with_description( 'A description' )
			->with_type( $env->get_type_registry()->definition( 'uint' ) )
			->with_before_set( $func )
			->with_map_from( '__example' )
			->with_dto_name( 'example_dto' )
			->with_kind( MT_Field_Declaration::META )
			->with_serializer( $func )
			->with_deserializer( $func )
			->with_sanitizer( $func )
			->with_updater( $func )
			->with_before_get( $func )
			->with_before_set( $func )
			->with_required( true );
		$field_declaration = $builder->build();

		$this->assertEquals( 'example', $field_declaration->get_name() );
		$this->assertSame( $env->get_type_registry()->definition( 'uint' ), $field_declaration->get_type() );
		$this->assertSame( MT_Field_Declaration::META, $field_declaration->get_kind() );
		$this->assertSame( 'A description', $field_declaration->get_description() );
		$this->assertSame( 'example_dto', $field_declaration->get_data_transfer_name() );

		$this->assertSame( '__example', $field_declaration->get_map_from() );

		$this->assertSame( $func, $field_declaration->before_set() );

		$this->assertSame( $func, $field_declaration->get_serializer() );
		$this->assertSame( $func, $field_declaration->get_deserializer() );
		$this->assertSame( $func, $field_declaration->get_sanitizer() );
		$this->assertSame( $func, $field_declaration->before_get() );
		$this->assertSame( $func, $field_declaration->before_set() );
	}

	function some_callable() {
	}
}
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MT_Data_Serializer {
	private $model_delegate;

	/**
	 * Mixtape_Data_Serializer constructor.
	 *
	 * @param MT_Model_Definition $model_definition
	 */
	function __construct( $model_definition ) {
		$this->model_delegate = $model_definition->get_model_declaration();
	}

	/**
	 * @param MT_Model_Field_Declaration $field_declaration
	 * @param mixed                           $value
	 * @return mixed the deserialized value
	 */
	function deserialize( $field_declaration, $value ) {
		$deserializer = $field_declaration->get_deserializer();
		return $deserializer ? $this->model_delegate->call( $deserializer, array( $value ) ) : $value;
	}

	/**
	 * @param  MT_Model_Field_Declaration $field_declaration
	 * @param mixed                           $value
	 * @return mixed
	 * @throws MT_Exception
	 */
	function serialize( $field_declaration, $value ) {
		$serializer = $field_declaration->get_serializer();
		if ( isset( $serializer ) && ! empty( $serializer ) ) {
			return $this->model_delegate->call( $serializer, array( $value ) );
		}
		return $value;
	}
}

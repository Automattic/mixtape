<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mixtape_Type_TypedArray extends Mixtape_Type {

	private $item_type_definition;

	/**
	 * Mixtape_TypeDefinition_TypedArray constructor.
	 *
	 * @param Mixtape_Interfaces_Type $item_type_definition
	 */
	function __construct( $item_type_definition ) {
		parent::__construct( 'array:' . $item_type_definition->name() );
		$this->item_type_definition = $item_type_definition;
	}

	public function default_value() {
		return array();
	}

	public function cast( $value ) {
		$new_value = array();

		foreach ( $value as $v ) {
			$new_value[] = $this->item_type_definition->cast( $v );
		}
		return (array) $new_value;
	}

	function schema() {
		$schema = parent::schema();
		$schema['type'] = 'array';
		$schema['items'] = $this->item_type_definition->schema();
	}
}

<?php

/**
 * Class Casette_Settings
 */
class Casette_Settings extends MT_Model_Settings {
	/**
	 * @param string $field_name
	 * @param MT_Field_Declaration_Builder $field_builder
	 * @param array $field_data
	 * @param MT_Environment $env
	 */
	function on_field_setup( $field_name, $field_builder, $field_data, $env ) {
		$field_builder->with_dto_name( str_replace( 'mixtape_casette_', '', $field_data['name'] ) );
	}

	/**
	 * @return array
	 */
	public function get_settings() {
		return Casette_Admin_Settings::get_settings();
	}
}
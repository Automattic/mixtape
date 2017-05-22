<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MT_Data_Mapper_SettingsToModels {
	/**
	 * @var MT_Model_Definition
	 */
	private $definition;

	/**
	 * Mixtape_Data_Mapper_SettingsArrayToModels constructor.
	 *
	 * @param MT_Model_Definition $definition
	 * @param Callable                 $settings_array_provider
	 */
	function __construct( $definition, $settings_array_provider ) {
		$this->definition = $definition;
		$this->settings_array_provider = $settings_array_provider;
	}

	/**
	 * @throws MT_Exception
	 */
	function map() {
		$settings_per_group = call_user_func( $this->settings_array_provider );
		$fields = array();

		foreach ( $settings_per_group as $group_name => $group_data ) {
			$group_description = $group_data[0];
			$group_fields = $group_data[1];

			foreach ( $group_fields as $field_data ) {

				$field_name = $field_data['name'];
				$field_builder = $def->field( $field_name );
				$field_dto_name = str_replace( 'job_manager_', '', $field_name );
				$default_value = isset( $field_data['std'] ) ? $field_data['std'] : null;
				$label         = isset( $field_data['label'] ) ? $field_data['label'] : $field_name;
				$description   = isset( $field_data['desc'] ) ? $field_data['desc'] : $label;
				$setting_type  = isset( $field_data['type'] ) ? $field_data['type'] : null;
				$choices       = isset( $field_data['options'] ) ? $field_data['options'] : null;
				$field_type = 'string';

				if ( 'checkbox' === $setting_type ) {
					$field_type = 'boolean';
					if ( $default_value ) {
						// convert our default value as well
						$default_value = $declaration->call( 'bit_to_bool', $default_value );
					}
					$field_builder
						->with_serializer( 'bool_to_bit' )
						->with_deserializer( 'bit_to_bool' );

				} elseif ( 'select' === $setting_type ) {
					$field_type = 'string';
				} else {
					// try to guess numeric fields, although this is not perfect
					if ( is_numeric( $default_value ) ) {
						$field_type = is_float( $default_value ) ? 'float' : 'integer';
					}
				}

				if ( $default_value ) {
					$field_builder->with_default( $default_value );
				}
				$field_builder
					->description( $label )
					->dto_name( $field_dto_name )
					->typed( $def->type( $field_type ) );
				if ( $choices ) {
					$field_builder->choices( $choices );
				}

				$fields[] = $field_builder;
			}// End foreach().
		}// End foreach().
		return $fields;
	}

	function bool_to_bit( $value ) {
		return ( ! empty( $value ) && 'false' !== $value) ? '1' : '';
	}

	function bit_to_bool( $value ) {
		return ( ! empty( $value ) && '0' !== $value ) ? true : false;
	}
}

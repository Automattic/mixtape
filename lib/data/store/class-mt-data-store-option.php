<?php
/**
 * Data Store Abstract
 *
 * @package MT/Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Data_Store_Option
 */
class MT_Data_Store_Option extends MT_Data_Store_Abstract
	implements MT_Interfaces_Data_Store {

	/**
	 * A guard value to distinguish between get_option returning results or not
	 *
	 * @var stdClass
	 */
	private $does_not_exist_guard;

	/**
	 * MT_Data_Store_Option constructor.
	 *
	 * @param MT_Model_Definition $definition Def.
	 * @param array               $args Args.
	 */
	public function __construct( $definition, $args = array() ) {
		parent::__construct( $definition, $args );
		$this->does_not_exist_guard = new stdClass();
	}

	/**
	 * Get Entities
	 *
	 * @param null|mixed $filter Filter.
	 * @return MT_Interfaces_Model
	 */
	public function get_entities( $filter = null ) {
		// there is only one option bag and one option bag global per data store.
		return $this->get_entity( '' );
	}

	/**
	 * Get Entity
	 *
	 * @param int $id The id of the entity.
	 * @return MT_Interfaces_Model
	 */
	public function get_entity( $id ) {
		$field_declarations = $this->get_definition()->get_field_declarations();
		$raw_data = array();
		foreach ( $field_declarations as $field_declaration ) {
			/**
			 * Field Declaration
			 *
			 * @var MT_Field_Declaration $field_declaration
			 */
			$option = get_option( $field_declaration->get_map_from(), $this->does_not_exist_guard );
			if ( $this->does_not_exist_guard !== $option ) {
				$raw_data[ $field_declaration->get_map_from() ] = $option;
			}
		}

		$data = $this->get_data_mapper()
			->raw_data_to_model_data( $raw_data, $field_declarations );
		return $this->get_definition()->create_instance( $data );
	}

	/**
	 * Delete
	 *
	 * @param MT_Interfaces_Model $model Model.
	 * @param array               $args Args.
	 * @return mixed
	 */
	public function delete( $model, $args = array() ) {
		$options_to_delete = array_keys( $this->get_data_mapper()->model_to_data( $model ) );
		foreach ( $options_to_delete as $option_to_delete ) {
			if ( false !== get_option( $option_to_delete, false ) ) {
				delete_option( $option_to_delete );
			}
		}
		return true;
	}

	/**
	 * Update/Insert
	 *
	 * @param MT_Interfaces_Model $model Model.
	 * @return mixed
	 */
	public function upsert( $model ) {
		$fields_for_insert = $this->get_data_mapper()->model_to_data( $model );
		foreach ( $fields_for_insert as $option_name => $option_value ) {
			$previous_value = get_option( $option_name, $this->does_not_exist_guard );
			if ( $this->does_not_exist_guard !== $previous_value ) {
				update_option( $option_name, $option_value );
			} else {
				add_option( $option_name, $option_value );
			}
		}
	}
}

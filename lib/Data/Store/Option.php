<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Data_Store_Option extends Mixtape_Data_Store_Abstract implements Mixtape_Interfaces_Data_Store {
    /**
     * @var stdClass a guard value
     */
    private $does_not_exist_guard;

    /**
     * @return Mixtape_Model_Collection|Mixtape_Model
     */
    public function get_entities() {
        $this->does_not_exist_guard = new stdClass();
        // there is only one option bag and one optionbag global per data store
        return $this->get_entity( '' );
    }

    /**
     * @param int $id the id of the entity
     * @return Mixtape_Model
     */
    public function get_entity( $id ) {
        // TODO: Implement get_entity() method.
        $field_declarations = $this->get_definition()->get_field_declarations();
        $raw_data = array();
        foreach ($field_declarations as $field_declaration) {
            /** @var Mixtape_Model_Field_Declaration  $field_declaration */
            $option = get_option( $field_declaration->get_map_from(), $this->does_not_exist_guard );
            if ($this->does_not_exist_guard !== $option ) {
                $raw_data[$field_declaration->get_map_from()] = $option;
            }
        }

        $data = $this->get_data_mapper()
            ->raw_data_to_model_data( $raw_data, $field_declarations );
        return $this->get_definition()->create_instance( $data );
    }

    /**
     * @param $entity Mixtape_Model
     * @param $field_declaration Mixtape_Model_Field_Declaration
     * @return mixed
     */
    public function get_meta_field_value( $model, $field_declaration ) {
        // no metafields
        return '';
    }

    /**
     * @param $model Mixtape_Model
     * @param array $args
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
     * @param $model Mixtape_Model
     * @return mixed
     */
    public function upsert( $model ) {
        $fields_for_insert = $this->get_data_mapper()->model_to_data( $model );
        foreach ($fields_for_insert as $option_name => $option_value ) {
            if ( $this->does_not_exist_guard !== get_option( $option_name, $this->does_not_exist_guard )  ) {
                update_option( $option_name, $option_value );
            } else {
                add_option( $option_name, $option_value );
            }
        }
    }
}
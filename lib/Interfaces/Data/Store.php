<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Interface Mixtape_Interfaces_Data_Store
 */
interface Mixtape_Interfaces_Data_Store {

    /**
     * @return Mixtape_Model_Collection
     */
    public function get_entities();


    /**
     * @param $id the id of the entity
     * @return Mixtape_Model
     */
    public function get_entity( $id );


    /**
     * @param $entity Mixtape_Model
     * @param $field_declaration Mixtape_Model_Field_Declaration
     * @return mixed
     */
    public function get_meta_field_value( $model, $field_declaration );

    /**
     * @param $model Mixtape_Model
     * @param array $args
     * @return mixed
     */
    public function delete( $model, $args = array() );

    /**
     * @param $entity Mixtape_Model
     * @return mixed
     */
    public function upsert( $entity, $fields, $meta_fields = array() );

    public function is_nil();
}
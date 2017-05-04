<?php

/**
 * Class Mixtape_Data_Store_Nil
 * Null object for datastores
 */
class Mixtape_Data_Store_Nil implements Mixtape_Interfaces_Data_Store {

    /**
     * @return Mixtape_Model_Collection
     */
    public function get_entities() {
        return new Mixtape_Model_Collection( array() );
    }

    /**
     * @param $id the id of the entity
     * @return Mixtape_Model
     */
    public function get_entity( $id ) {
        return null;
    }

    /**
     * @param $entity Mixtape_Model
     * @param $field_declaration Mixtape_Model_Field_Declaration
     * @return mixed
     */
    public function get_meta_field_value( $model, $field_declaration ) {
        return '';
    }

    /**
     * @param $model Mixtape_Model
     * @param array $args
     * @return mixed
     */
    public function delete( $model, $args = array()) {
        return true;
    }

    /**
     * @param $model Mixtape_Model
     * @return mixed
     */
    public function upsert( $model ) {
        return 0;
    }

    public function set_definition( $definition ) {
    }
}
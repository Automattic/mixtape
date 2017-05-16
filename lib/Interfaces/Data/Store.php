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
     * @param int $id the id of the entity
     * @return Mixtape_Model
     */
    public function get_entity( $id );

    /**
     * @param $model Mixtape_Model
     * @param array $args
     * @return mixed
     */
    public function delete( $model, $args = array() );

    /**
     * @param $model Mixtape_Model
     * @return mixed
     */
    public function upsert($model );

    /**
     * @param Mixtape_Model_Definition $definition
     * @return Mixtape_Interfaces_Data_Store $this
     */
    public function set_definition( $definition );
}
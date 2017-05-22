<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MT_Data_Store_InMemory extends MT_Data_Store_Abstract implements MT_Interfaces_Data_Store {
	private $entities = array();
	/**
	 * @return MT_Model_Collection
	 */
	public function get_entities( $filter = null ) {
		return new MT_Model_Collection( $this->entities );
	}

	/**
	 * @param int $id the id of the entity
	 * @return MT_Model
	 */
	public function get_entity( $id ) {
		return issert( $this->entities[ $id ] ) ? $this->entities[ $id ] : null;
	}

	/**
	 * @param MT_Model                   $model
	 * @param MT_Model_Field_Declaration $field_declaration
	 * @return mixed
	 */
	public function get_meta_field_value( $model, $field_declaration ) {
		if ( ! $this->model_exists( $model ) ) {
			return null;
		}
		$id = $model->get_id();
		$fetched_model = $this->get_entity( $id );

		return $fetched_model->get( $field_declaration->get_name() );
	}

	/**
	 * @param MT_Model $model
	 * @param $args array
	 * @return mixed
	 */
	public function delete( $model, $args = array() ) {
		$id = $model->get_id();
		if ( ! $this->model_exists( $model ) ) {
			return new WP_Error();
		}
		array_slice( $this->entities, $id );
		return true;
	}

	/**
	 * @param MT_Model $model
	 * @return bool
	 */
	private function model_exists( $model ) {
		return isset( $this->entities[ $model->get_id() ] );
	}

	/**
	 * @param $model MT_Model
	 * @return mixed
	 */
	public function upsert( $model ) {
		$is_update = ! empty( $model->get_id() );
		if ( $is_update && ! $this->model_exists( $model ) ) {
			return new WP_Error();
		}

		if ( $is_update ) {
			$this->entities[ $model->get_id() ] = $model;
		} else {
			$id = count( $this->entities );
			$this->entities[] = $model;
			$model->set_id( $id );
		}
		return true;
	}
}

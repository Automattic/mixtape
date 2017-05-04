<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Data_Store_Cpt implements Mixtape_Interfaces_Data_Store {
    /**
     * @var Mixtape_Interfaces_Model_Delegate
     */
    private $model_delegate;
    /**
     * @var Mixtape_Model_Definition
     */
    private $definition;
    /**
     * @var string
     */
    private $post_type;

    /**
     * Mixtape_Data_Store_Cpt constructor.
     * @param string $post_type
     */
    public function __construct( $post_type = null ) {
        $this->post_type = empty( $post_type ) ? 'post' : $post_type;
    }

    /**
     * @param Mixtape_Model_Definition $definition
     * @return Mixtape_Interfaces_Data_Store $this
     */
    public function set_definition( $definition ) {
        $this->definition = $definition;
        $this->model_delegate = $definition->get_delegate();
        return $this;
    }

    /**
     * @return Mixtape_Model_Collection
     */
    public function get_entities() {
        $query = new WP_Query( array(
            'post_type' => $this->post_type,
            'post_status' => 'any'
        ) );
        $posts = $query->get_posts();
        $collection = array();
        foreach ( $posts as $post ) {
            $collection[] = $this->definition->create_instance( $post );
        }
        return new Mixtape_Model_Collection( $collection );
    }

    /**
     * @param int $id the id of the entity
     * @return Mixtape_Model|null
     */
    public function get_entity( $id ) {
        $post = get_post( absint( $id ) );
        return !empty( $post ) && $post->post_type === $this->post_type ? $this->definition->create_instance( $post->to_array() ) : null;
    }

    /**
     * @param Mixtape_Model $model
     * @param Mixtape_Model_Field_Declaration $field_declaration
     * @return mixed
     */
    public function get_meta_field_value( $model, $field_declaration ) {
        $map_from = $field_declaration->get_name_to_map_from();
        return get_post_meta( $model->get_id(), $map_from, true );
    }

    /**
     * @param $model Mixtape_Model
     * @param array $args
     * @return mixed
     */
    public function delete($model, $args = array()) {
        $id = $model->get_id();

        $args = wp_parse_args( $args, array(
            'force_delete' => false,
        ) );

        if ( $args['force_delete'] ) {
            wp_delete_post( $model->get_id() );
            $model->set( 'id', 0 );
            do_action( 'mixtape_data_store_delete_model', $model, $id );
        } else {
            wp_trash_post( $model->get_id() );
            $model->set( 'status', 'trash' );
            do_action( 'mixtape_data_store_trash_model', $model, $id );
        }
    }

    /**
     * @param Mixtape_Model $model
     * @return mixed|WP_Error
     */
    public function upsert( $model ) {
        $fields = $this->map_field_types_for_upserting( $model, Mixtape_Model_Field_Types::FIELD );
        $meta_fields = $this->map_field_types_for_upserting( $model, Mixtape_Model_Field_Types::META );
        if ( ! isset( $fields['post_type'] ) ) {
            $fields['post_type'] = $this->post_type;
        }
        $fields['meta_input'] = $meta_fields;
        $id_or_error = wp_insert_post( $fields, true );
        if ( is_wp_error( $id_or_error ) ) {
            return $id_or_error;
        }
        $model->set( 'id', absint( $id_or_error ) );

        return absint( $id_or_error );
    }

    /**
     * @param Mixtape_Model $model
     * @param $field_type
     * @return array
     */
    private function map_field_types_for_upserting( $model, $field_type ) {
        $field_values_to_insert = array();
        foreach ( $this->definition->get_field_declarations( $field_type ) as $field_declaration ) {
            /** @var Mixtape_Model_Field_Declaration $field_declaration */
            $what_to_map_to = $field_declaration->get_name_to_map_from();
            $field_values_to_insert[$what_to_map_to] = $model->get( $field_declaration->get_name() );
        }
        return $field_values_to_insert;
    }
}
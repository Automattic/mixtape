<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Data_Store_CustomPostType
    extends Mixtape_Data_Store_Abstract
    implements Mixtape_Interfaces_Data_Store {
    /**
     * @var string the post type name
     */
    private $post_type;
    /**
     * Load all of the cpt including meta in one go
     * @var bool
     */
    private $eager_load;

    /**
     * Mixtape_Data_Store_Cpt constructor.
     * @param string $post_type
     */
    public function __construct( $post_type = null, $args = array() ) {
        $this->post_type = empty( $post_type ) ? 'post' : $post_type;
        $this->eager_load = isset( $args[ 'eager_load' ] ) ? (bool)$args['eager_load'] : true;
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
            $collection[] = $this->create_from_post( $post );
        }
        return new Mixtape_Model_Collection( $collection );
    }

    /**
     * @param int $id the id of the entity
     * @return Mixtape_Model|null
     */
    public function get_entity( $id ) {
        $post = get_post( absint( $id ) );
        if ( empty( $post ) || $post->post_type !== $this->post_type ) {
            return null;
        }

        return $this->create_from_post( $post );
    }

    /**
     * @param WP_Post $post
     * @return Mixtape_Model
     * @throws Mixtape_Exception
     */
    private function create_from_post( $post ) {
        $post_arr = $post->to_array();
        $field_declarations = $this->get_definition()->get_field_declarations( Mixtape_Model_Field_Types::FIELD );
        $raw_data = $this->get_data_mapper()->raw_data_to_model_data( $post_arr, $field_declarations );

        if ( $this->eager_load ) {
            $meta = get_post_meta( $post->ID );

            $flattened_meta = array();
            foreach ($meta as $key => $value_arr ) {
                $flattened_meta[$key] = $value_arr[0];
            }
            $meta_field_declarations = $this->get_definition()->get_field_declarations( Mixtape_Model_Field_Types::META );
            $raw_meta_data = $this->get_data_mapper()->raw_data_to_model_data( $flattened_meta, $meta_field_declarations );
            $raw_data = array_merge($raw_data, $raw_meta_data );
        }

        return $this->get_definition()->create_instance( $raw_data );
    }

    /**
     * @param Mixtape_Model $model
     * @param Mixtape_Model_Field_Declaration $field_declaration
     * @return mixed
     */
    public function get_meta_field_value( $model, $field_declaration ) {
        $map_from = $field_declaration->get_map_from();
        $value = get_post_meta( $model->get_id(), $map_from, true );
        if ( empty( $value ) ) {
            return $field_declaration->get_default_value();
        }
        return $this->get_serializer()->deserialize( $field_declaration, $value );
    }

    /**
     * @param Mixtape_Model $model
     * @param array $args
     * @return mixed
     */
    public function delete($model, $args = array()) {
        $id = $model->get_id();

        $args = wp_parse_args( $args, array(
            'force_delete' => false,
        ) );

        do_action( 'mixtape_data_store_delete_model_before', $model, $id );

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
        $updating = ! empty( $model->get_id() );
        $fields = $this->get_data_mapper()->model_to_data( $model, Mixtape_Model_Field_Types::FIELD );
        $meta_fields = $this->get_data_mapper()->model_to_data( $model, Mixtape_Model_Field_Types::META );
        if ( ! isset( $fields['post_type'] ) ) {
            $fields['post_type'] = $this->post_type;
        }
        if (isset( $fields['ID'] ) && empty( $fields['ID'] ) ) {
            // ID of 0 is not acceptable on CPTs, so remove it
            unset( $fields['ID'] );
        }

        do_action( 'mixtape_data_store_model_upsert_before', $model );

        $id_or_error = wp_insert_post( $fields, true );
        if ( is_wp_error( $id_or_error ) ) {
            do_action( 'mixtape_data_store_model_upsert_error', $model );
            return $id_or_error;
        }
        $model->set( 'id', absint( $id_or_error ) );
        foreach ( $meta_fields as $meta_key => $meta_value ) {
            if ( $updating ) {
                $id_or_bool = update_post_meta( $id_or_error, $meta_key, $meta_value );
            } else {
                $id_or_bool = add_post_meta( $id_or_error, $meta_key, $meta_value );
            }

            if ( false === $id_or_bool ) {
                do_action( 'mixtape_data_store_model_upsert_error', $model );
                // Something was wrong with this update/create. TODO: Should we stop mid create/update?
                return new WP_Error(
                    'mixtape-error-creating-meta',
                    'There was an error updating/creating an entity field',
                    array(
                        'field_key' => $meta_key,
                        'field_value' => $meta_value
                    )
                );
            }
        }

        do_action( 'mixtape_data_store_model_upsert_after', $model );

        return absint( $id_or_error );
    }
}
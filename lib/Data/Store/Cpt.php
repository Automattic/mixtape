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
        $field_declarations = $this->definition->get_field_declarations( Mixtape_Model_Field_Types::FIELD );
        $raw_data = $this->map_data( $post_arr, $field_declarations );

        if ( $this->eager_load ) {
            $meta = get_post_meta( $post->ID );

            $flattened_meta = array();
            foreach ($meta as $key => $value_arr ) {
                $flattened_meta[$key] = $value_arr[0];
            }
            $meta_field_declarations = $this->definition->get_field_declarations( Mixtape_Model_Field_Types::META );
            $raw_meta_data = $this->map_data( $flattened_meta, $meta_field_declarations );
            $raw_data = array_merge($raw_data, $raw_meta_data );
        }

        return $this->definition->create_instance( $raw_data );
    }

    private function map_data( $data, $field_declarations ) {
        $raw_data = array();
        $post_array_keys = array_keys( $data );
        foreach ( $field_declarations as $declaration ) {
            /** @var Mixtape_Model_Field_Declaration $declaration */
            $key = $declaration->get_name();
            $mapping = $declaration->get_name_to_map_from();
            $value = null;
            if ( in_array( $key, $post_array_keys ) ) {
                // simplest case: we got a $key for this, so just map it
                $value = $this->deserialize_value( $declaration, $data[$key] );
            } else if (in_array( $mapping, $post_array_keys ) ) {
                $value = $this->deserialize_value( $declaration, $data[$mapping] );
            } else {
                $value = $declaration->get_default_value();
            }
            $raw_data[$key] = $declaration->cast_value( $value );
        }
        return $raw_data;
    }

    /**
     * @param Mixtape_Model $model
     * @param Mixtape_Model_Field_Declaration $field_declaration
     * @return mixed
     */
    public function get_meta_field_value( $model, $field_declaration ) {
        $map_from = $field_declaration->get_name_to_map_from();
        $value = get_post_meta( $model->get_id(), $map_from, true );
        if ( empty( $value ) ) {
            return $field_declaration->get_default_value();
        }
        return $this->deserialize_value( $field_declaration, $value );
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
        $updating = !empty( $model->get_id() );
        $fields = $this->prepare_for_upsert( $model, Mixtape_Model_Field_Types::FIELD );
        $meta_fields = $this->prepare_for_upsert( $model, Mixtape_Model_Field_Types::META );
        if ( ! isset( $fields['post_type'] ) ) {
            $fields['post_type'] = $this->post_type;
        }
        if (isset( $fields['ID'] ) && empty( $fields['ID'] ) ) {
            // ID of 0 is not acceptable
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

    /**
     * @param Mixtape_Model $model
     * @param $field_type
     * @return array
     */
    private function prepare_for_upsert( $model, $field_type ) {
        $field_values_to_insert = array();
        foreach ( $this->definition->get_field_declarations( $field_type ) as $field_declaration ) {
            /** @var Mixtape_Model_Field_Declaration $field_declaration */
            $serializer = $field_declaration->get_serializer();
            $what_to_map_to = $field_declaration->get_name_to_map_from();
            $key = $field_declaration->get_name();
            $value = $model->get( $key );
            if ( isset( $serializer ) && !empty( $serializer ) ) {
                $value = $this->model_delegate->call( $serializer, array( $value ) );
            }
            $field_values_to_insert[$what_to_map_to] = $value;
        }

        return $field_values_to_insert;
    }

    private function deserialize_value( $field_declaration, $value ) {
        $deserializer = $field_declaration->get_deserializer();
        return $deserializer ? $this->model_delegate->call( $deserializer, array( $value ) ) : $value;
    }
}
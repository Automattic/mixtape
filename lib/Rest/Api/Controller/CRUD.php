<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class Mixtape_Rest_Api_Controller_CRUD extends Mixtape_Rest_Api_Controller {
    /**
     * @var Mixtape_Model_Definition
     */
    private $model_definition;
    /**
     * @var Mixtape_Model_Delegate
     */
    private $model_delegate;
    /**
     * @var Mixtape_Interfaces_Data_Store
     */
    private $model_data_store;

    /**
     * Mixtape_Rest_Api_Controller_CRUD constructor.
     * @param Mixtape_Rest_Api_Controller_Bundle $api
     * @param Mixtape_Model_Definition $model_definition
     */
    public function __construct( $api, $base, $model_definition ) {
        $this->base = $base;
        $environment = $model_definition->get_environment();
        parent::__construct( $api, $environment );
        $this->model_definition = $model_definition;
        $this->model_delegate = $this->model_definition->get_delegate();
        $this->model_data_store = $this->model_definition->get_data_store();
    }

    public function register() {
        $prefix = $this->controller_bundle->get_bundle_prefix();
        register_rest_route( $prefix, $this->base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_item' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            )
        ) );
        register_rest_route( $prefix,  $this->base . '/(?P<id>\d+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_item' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE ),
            ),
        ) );
    }

    public function get_items( $request ) {
        $item_id = isset( $request['id'] ) ? absint( $request['id'] ) : null;

        if (null === $item_id ) {
            $models = $this->model_definition->all();
            $data = $this->prepare_dto( $models );
            return $this->succeed( $data );
        }

        $model = $this->model_definition->find_one_by_id($item_id);
        if ( empty( $model ) ) {
            return $this->not_found( __( 'Model not found' ) );
        }

        return $this->succeed( $this->prepare_dto( $model ) );
    }

    /**
     * @param $entity array|Mixtape_Model_Collection|Mixtape_Model
     * @return array
     */
    protected function prepare_dto( $entity ) {
        if ( is_array( $entity ) ) {
            return $entity;
        }

        if ( is_a( $entity, 'Mixtape_Model_Collection' ) ) {
            $results = array();
            foreach ( $entity->get_items() as $model ) {
                $results[] = $this->model_to_dto( $model );
            }
            return $results;
        }

        if ( is_a( $entity, 'Mixtape_Model' ) ) {
            return $this->model_to_dto( $entity );
        }

        return $entity;
    }

    /**
     * @param Mixtape_Model $model
     * @return array
     */
    protected function model_to_dto( $model ) {
        $result = array();
        foreach ($this->model_definition->get_data_transfer_object_field_mappings() as $mapping_name => $field_name ) {
            $value = $model->get( $field_name );
            $result[$mapping_name] = $value;
        }
        $result['_links'] = $this->add_links( $model );
        return $result;
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_item( $request ) {
        $is_update = false;
        return $this->create_or_update( $request, $is_update );
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_item($request) {
        $is_update = true;
        return $this->create_or_update( $request, $is_update );
    }

    /**
     * @param WP_REST_Request $request
     * @param bool $is_update
     * @return WP_REST_Response
     */
    protected function create_or_update( $request, $is_update = false ) {
        $model_to_update = null;
        if ( $is_update ) {
            $id = isset( $request['id'] ) ? absint( $request['id'] ) : null;
            if ( ! empty( $id ) ) {
                $model_to_update = $this->model_definition->find_one_by_id( $id );
                if ( empty( $model_to_update ) ) {
                    return $this->not_found( 'Model does not exist' );
                }
            }
        }

        if ( $is_update && $model_to_update ) {
            $model = $this->model_definition->merge_updates_from_request( $model_to_update, $request, $is_update );
        } else {
            $model = $this->prepare_item_for_database( $request );
        }

        if ( is_wp_error( $model ) ) {
            $wp_err = $model;
            return $this->fail_with( $wp_err );
        }

        $validation = $model->validate();
        if ( is_wp_error( $validation )  ) {
            return $this->fail_with( $validation );
        }

        $id_or_error = $this->model_data_store->upsert( $model );

        if ( is_wp_error( $id_or_error ) ) {
            return $this->fail_with( $id_or_error );
        }

        return $this->created( $this->prepare_dto( array('id' => absint( $id_or_error ) ) ) );
    }

    public function delete_item( $request ) {
        $id = isset( $request['id'] ) ? absint( $request['id'] ) : null;
        if ( empty( $id ) ) {
            return $this->fail_with( 'No Model ID provided' );
        }
        $model = $this->model_definition->find_one_by_id( $id );
        if ( null === $model ) {
            return $this->not_found( 'Model does not exist' );
        }
        $result = $this->model_data_store->delete( $model );
        return $this->succeed( $result );
    }

    /**
     * Prepare the item for create or update operation.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        return $this->model_definition->new_from_request( $request) ;
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function get_items_permissions_check( $request ) {
        return $this->admin_permissions_check( $request );
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function create_item_permissions_check( $request ) {
        return $this->admin_permissions_check( $request );
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function delete_item_permissions_check( $request ) {
        return $this->admin_permissions_check( $request );
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    private function admin_permissions_check( $request ) {
        // we are only going to allow admins to access the rest api for now
        return true;
    }

    /**
     * @param Mixtape_Model $model
     * @return array
     */
    protected function add_links( $model ) {
        $base_url = rest_url() . '/' . $this->controller_bundle->get_bundle_prefix() . '/' . $this->base . '/';
        return array(
            'self' => array(
                array(
                    'href' => esc_url( $base_url . $model->get_id() )
                )
            ),
            'collection' => array(
                array(
                    'href' => esc_url( $base_url . '/courses/' )
                )
            ),
            'author' => array(
                array(
                    'href' => esc_url( rest_url() . 'wp/v2/users/' . $model->get( 'author' ) )
                )
            )
        );
    }

    protected function get_base_url() {
        return rest_url( $this->controller_bundle->get_bundle_prefix(), $this->base );
    }

    /**
     * Retrieves the item's schema, conforming to JSON Schema.
     * @access public
     *
     * @return array Item schema data.
     */
    public function get_item_schema() {
        $fields = $this->model_definition->get_field_declarations();
        $properties = array();
        foreach ( $fields as $field_declaration ) {
            /** @var Mixtape_Model_Field_Declaration $field_declaration */
            $properties[$field_declaration->get_data_transfer_name()] = $field_declaration->as_item_schema_property();
        }
        $schema = array(
            '$schema'    => 'http://json-schema.org/schema#',
            'title'      => 'course',
            'type'       => 'object',
            'properties' => (array)apply_filters( 'mixtape_rest_api_schema_properties', $properties, $this->model_definition )
        );

        return $this->add_additional_fields_schema( $schema );
    }
}
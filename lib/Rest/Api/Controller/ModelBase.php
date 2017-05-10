<?php

/**
 * Class Mixtape_Rest_Api_Controller_ModelBase
 * Knows about models
 */
class Mixtape_Rest_Api_Controller_ModelBase extends Mixtape_Rest_Api_Controller {
    /**
     * @var Mixtape_Model_Definition
     */
    protected $model_definition;
    /**
     * @var Mixtape_Model_Declaration
     */
    protected $model_declaration;
    /**
     * @var Mixtape_Interfaces_Data_Store
     */
    protected $model_data_store;

    /**
     * Mixtape_Rest_Api_Controller_CRUD constructor.
     * @param Mixtape_Rest_Api_Controller_Bundle $controller_bundle
     * @param Mixtape_Model_Definition $model_definition
     */
    public function __construct($controller_bundle, $base, $model_definition)
    {
        $this->base = $base;
        $environment = $model_definition->get_environment();
        parent::__construct($controller_bundle, $environment);
        $this->model_definition = $model_definition;
        $this->model_declaration = $this->model_definition->get_delegate();
        $this->model_data_store = $this->model_definition->get_data_store();
    }


    protected function get_model_definition() {
        return $this->model_definition;
    }

    /**
     * Retrieves the item's schema, conforming to JSON Schema.
     * @access public
     *
     * @return array Item schema data.
     */
    public function get_item_schema() {
        $fields = $this->get_model_definition()->get_field_declarations();
        $properties = array();
        foreach ($fields as $field_declaration) {
            /** @var Mixtape_Model_Field_Declaration $field_declaration */
            $properties[$field_declaration->get_data_transfer_name()] = $field_declaration->as_item_schema_property();
        }
        $schema = array(
            '$schema' => 'http://json-schema.org/schema#',
            'title' => 'course',
            'type' => 'object',
            'properties' => (array)apply_filters( 'mixtape_rest_api_schema_properties', $properties, $this->get_model_definition() )
        );

        return $this->add_additional_fields_schema( $schema );
    }

    /**
     * @return Mixtape_Model_Declaration
     */
    protected function get_model_declaration() {
        return $this->model_declaration;
    }

    /**
     * @return Mixtape_Interfaces_Data_Store
     */
    protected function get_model_data_store() {
        return $this->model_data_store;
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function get_items_permissions_check( $request ) {
        return $this->permissions_check( $request, 'index' );
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function get_item_permissions_check( $request ) {
        return $this->permissions_check( $request, 'show' );
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function create_item_permissions_check( $request ) {
        return $this->permissions_check( $request, 'create' );
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function update_item_permissions_check( $request ) {
        return $this->permissions_check( $request, 'update' );
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function delete_item_permissions_check( $request ) {
        return $this->permissions_check( $request, 'delete' );
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    private function permissions_check($request, $action ) {
        return $this->get_model_definition()->permissions_check( $request, $action );
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
    protected function model_to_dto($model) {
        $result = array();
        foreach ($this->get_model_definition()->get_dto_field_mappings() as $mapping_name => $field_name ) {
            $value = $model->get( $field_name );
            $result[$mapping_name] = $value;
        }

        return $result;
    }

    /**
     * Prepare the item for create or update operation.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database($request) {
        return $this->get_model_definition()->new_from_request( $request );
    }

    protected function get_base_url() {
        return rest_url($this->controller_bundle->get_bundle_prefix(), $this->base);
    }
}
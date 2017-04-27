<?php


class Mixtape_Model_Factory {


    /**
     * @var Sensei_Domain_Models_Registry
     */
    private $registry;
    /**
     * @var string|object
     */
    private $klass;

    function __construct( $klass, $registry ) {
        $this->registry = $registry;
        $this->klass = $klass;
    }

    /**
     * @param $request
     * @return Sensei_Domain_Models_Model_Abstract
     */
    public function new_from_request( $request )
    {
        $fields = $this->registry->get_field_declarations( $this->klass );
        $field_data = array();
        foreach ($fields as $field) {
            if (isset($request[$field->name])) {
                $field_data[$field->name] = $request[$field->name];
            } else {
                $field_data[$field->name] = $field->get_default_value();
            }
        }

        return $this->create_object( $field_data );
    }

    public function all() {
        $results = array();
        foreach ($this->get_entities() as $entity) {
            $results[] = $this->create_object($entity);
        }
        return new Sensei_Domain_Models_Model_Collection($results);
    }

    public function find_one_by_id( $id) {
        $entity = $this->get_datastore()->get_entity( $id );
        return !empty($entity) ? $this->create_object( $entity ) : null;
    }

    public function get_field_declarations( $filter_by_type = null ) {
        return $this->registry->get_field_declarations( $this->klass, $filter_by_type );
    }

    /**
     * @param $id the unique_id
     * @throws Mixtape_Exception
     * return Mixtape_Model|null
     */
    public function get_entity( $id) {
        return $this->get_data_store()->get_entity( $id );
    }

    /**
     * @throws Mixtape_Exception
     * @return array
     */
    public function get_entities() {
        return $this->get_data_store()->get_entities();
    }

    /**
     * @return Mixtape_Interfaces_Data_Store
     * @throws Mixtape_Exception
     */
    public function get_data_store() {
        return Mixtape_Environment::get_instance()
            ->get_data_store_for_domain_model( $this->klass );
    }

    public function create_object( $entity) {
        $klass = $this->get_domain_model_class( $this->klass );
        return new $klass( $entity );
    }

    private function get_domain_model_class( $thing ) {
        if (!is_string( $thing ) ) {
            $thing = get_class( $thing );
        }
        return $thing;
    }
}
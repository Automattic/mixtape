<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Type_Registry {
    private $container_types = array(
        'array',
        'nullable'
    );
    private $types = null;

    /**
     * @param string $identifier
     * @param Mixtape_Interfaces_Type $instance
     * @return Mixtape_Type_Registry $this
     * @throws Mixtape_Exception
     */
    public function define( $identifier, $instance ) {
        if ( !is_a( $instance, 'Mixtape_Interfaces_Type' ) ) {
            throw new Mixtape_Exception( '$instance is not a Mixtape_Interfaces_Type' );
        }
        $this->types[$identifier] = $instance;
        return $this;
    }

    /**
     * @param string $type
     * @return Mixtape_Interfaces_Type
     * @throws Mixtape_Exception
     */
    function definition($type ) {
        $types = $this->get_types();

        if ( ! isset( $types[$type] ) ) {
            // maybe lazy-register missing compound type
            $parts = explode( ':', $type );
            if ( count( $parts ) > 1 ) {

                $container_type = $parts[0];
                if ( ! in_array( $container_type, $this->container_types, true ) ) {
                   throw new Mixtape_Exception( $container_type . ' is not a known container type' );
                }

                $item_type = $parts[1];
                if (empty( $item_type ) ) {
                    throw new Mixtape_Exception( $type . ': invalid syntax' );
                }
                $item_type_definition = $this->definition( $item_type );

                if ('array' === $container_type ) {
                    $this->define( $type, new Mixtape_Type_TypedArray( $item_type_definition ) );
                    $types = $this->get_types();
                }

                if ( 'nullable' === $container_type ) {
                    $this->define( $type, new Mixtape_Type_Nullable( $item_type_definition ) );
                    $types = $this->get_types();
                }
            }
        }

        if ( ! isset( $types[$type] ) ) {
            throw new Mixtape_Exception();
        }
        return $types[$type];
    }

    private function get_types() {
        return apply_filters('mixtape_type_registry_get_types', $this->types, $this );
    }

    public function initialize( $environment ) {
        if ( null !== $this->types ) {
            return;
        }

        $this->types = apply_filters( 'mixtape_type_registry_register_types', array(
            'any'           => new Mixtape_Type( 'any' ),
            'string'        => new Mixtape_Type_String(),
            'integer'       => new Mixtape_Type_Integer(),
            'int'           => new Mixtape_Type_Integer(),
            'uint'          => new Mixtape_Type_Integer( true ),
            'number'        => new Mixtape_Type_Number(),
            'float'         => new Mixtape_Type_Number(),
            'boolean'       => new Mixtape_Type_Boolean(),
            'array'         => new Mixtape_Type_Array(),
        ), $this, $environment );
    }
}
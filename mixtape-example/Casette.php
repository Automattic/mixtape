<?php

class DoingItWrongDeclaration extends Mixtape_Model_Declaration {
}

class Casette extends Mixtape_Model_Declaration {
    public function declare_fields( $def ) {
            return array(
                $def->field( 'id' )
                    ->map_from( 'ID' )
                    ->of_type('integer')
                    ->with_description( 'Unique identifier for the object.' )
                    ->with_sanitize( 'as_uint' ),

                $def->field( 'title', 'The casette title.' )
                    ->map_from( 'post_title' )
                    ->of_type('string')
                    ->required(),

                $def->field( 'author', __( 'The author identifier.', 'casette' ) )
                    ->map_from( 'post_author' )
                    ->of_type('integer')
                    ->with_validations( 'validate_author' )
                    ->with_default( 0 )
                    ->dto_name( 'authorID' )
                    ->with_sanitize( 'as_uint' ),

                $def->field( 'status', 'The casette status.' )
                    ->of_type('string')
                    ->with_validations( 'validate_status' )
                    ->with_default('draft')
                    ->map_from( 'post_status' ),

                $def->derived_field( 'ratings', 'The casette ratings' )
                    ->map_from( 'get_ratings' )
                    ->dto_name( 'the_ratings' ),

                $def->meta_field( 'songs', 'The casette songs' )
                    ->map_from( '_casette_song_ids' )
                    ->of_type( 'array' )
                    ->with_default( array() )
                    ->with_deserializer( 'song_before_return' )
                    ->with_serializer( 'song_before_save' )
                    ->dto_name( 'song_ids' ),
            );
    }

    public function get_ratings( $model ) {
        return array( 1 );
    }

    public function get_id( $model ) {
        return $model->get( 'id' );
    }

    protected function validate_author( $model, $author_id ) {
        $author = $this->get_author( $author_id );
        if ( null === $author ) {
            return new WP_Error( 'invalid-author-id', __( 'Invalid author id', 'casette' ) );
        }
        return true;
    }

    protected function validate_status( $model, $status ) {
        if ('publish' === $status ) {
            $author_id = $model->get( 'author' );
            if ( empty( $author_id ) ) {
                return new WP_Error( 'missing-author-id', __( 'Cannot publish when author is empty', 'casette' ) );
            }
        }

        return true;
    }

    private function get_author( $author_id ) {
       return get_user_by( 'id', $author_id );
    }

    function song_before_return( $value ) {
        return array_map( 'absint', explode(',', $value ) );
    }

    function song_before_save( $value ) {
        return implode(',', $value );
    }
}

class Song extends Mixtape_Model_Declaration {
    public function declare_fields( $def ) {
        return array(
            $def->field()
                ->named( 'id' )
                ->map_from( 'ID' )
                ->of_type('integer')
                ->with_description( 'Unique identifier for the object.' )
                ->with_sanitize( 'as_uint' ),
            $def->field()
                ->named( 'title' )
                ->map_from( 'post_title' )
                ->of_type('string')
                ->with_description( 'The song title.' )
                ->required( true ),
        );
    }

    public function get_ratings() {
        return array();
    }
}

class CasetteApiEndpointVersion extends Mixtape_Rest_Api_Controller {
    /**
     * @var string the endpoint base
     */
    protected $base = '/version';

    public function register() {
        register_rest_route( $this->controller_bundle->get_bundle_prefix(),  $this->base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'                => array()
            )
        ) );
    }

    public function get_items( $request ) {
        return new WP_REST_Response( array( 'mixtape-example-version' => '0.1.0' ), 200 );
    }

    public function get_items_permissions_check( $request ) {
        return true;
    }
}

class CasetteApiBundleV1 extends Mixtape_Rest_Api_Controller_Bundle {
    protected $bundle_prefix = 'mixtape-example/v1';

    /**
     * Gets the endpoints, those can be extended by plugins by hooking into
     * `mixtape_rest_api_get_endpoints`
     * @return array
     */
    public function get_endpoints() {
        return array(
            new CasetteApiEndpointVersion( $this ),
        );
    }
}
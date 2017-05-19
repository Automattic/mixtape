<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DoingItWrongDeclaration extends Mixtape_Model_Declaration {
}

class CasetteAdminSettings {
    static function get_settings() {
        return array(
            'casette_general' => array(
                __( 'Casette General', 'mixtape' ),
                array(
                    array(
                        'name'        => 'mixtape_casette_per_page',
                        'std'         => '10',
                        'placeholder' => '',
                        'label'       => __( 'Casettes Per Page', 'mixtape' ),
                        'desc'        => __( 'How many listings should be shown per page by default?', 'mixtape' ),
                        'attributes'  => array()
                    ),
                    array(
                        'name'       => 'mixtape_casette_hide_listened',
                        'std'        => '0',
                        'label'      => __( 'Hide Listened Casettes', 'mixtape' ),
                        'cb_label'   => __( 'Hide Listened Casettes', 'mixtape' ),
                        'desc'       => __( 'If enabled, listened Casettes will be hidden from archives.', 'mixtape' ),
                        'type'       => 'checkbox',
                        'attributes' => array()
                    ),
                    array(
                        'name'       => 'mixtape_casette_enable_private',
                        'std'        => '0',
                        'label'      => __( 'Users can create private Casettes', 'mixtape' ),
                        'cb_label'   => __( 'Users can create private Casettes', 'mixtape' ),
                        'desc'       => __( 'If enabled, Users can create private Casettes (defaults to false)', 'mixtape' ),
                        'type'       => 'checkbox',
                        'attributes' => array()
                    ),
                )
            )
        );
    }
}

class CasetteSettings extends Mixtape_Model_Declaration_Settings {
    function dto_name_for_field( $field_data ) {
        return str_replace( 'mixtape_casette_', '', $field_data['name'] );
    }

    function get_settings() {
        return CasetteAdminSettings::get_settings();
    }
}

class Casette extends Mixtape_Model_Declaration
    implements Mixtape_Interfaces_Rest_Api_Permissions_Provider {
    public function declare_fields( $d ) {
            return array(
                $d->field( 'id' )
                    ->map_from( 'ID' )
                    ->typed( $d->type( 'uint') )
                    ->description( 'Unique identifier for the object.' ),

                $d->field( 'title', 'The casette title.' )
                    ->map_from( 'post_title' )
                    ->typed( $d->type( 'string') )
                    ->required(),

                $d->field( 'author', __( 'The author identifier.', 'casette' ) )
                    ->map_from( 'post_author' )
                    ->typed( $d->type( 'uint') )
                    ->validated_by( 'validate_author' )
                    ->with_default( 0 )
                    ->dto_name( 'authorID' ),

                $d->field( 'status', 'The casette status.' )
                    ->typed( $d->type( 'string') )
                    ->validated_by( 'validate_status' )
                    ->with_default('draft')
                    ->map_from( 'post_status' ),

                $d->field( 'ratings', 'The casette ratings' )
                    ->derived( 'get_ratings' )
                    ->dto_name( 'the_ratings' ),

                $d->field( 'songs', 'The casette songs', 'meta' )
                    ->map_from( '_casette_song_ids' )
                    ->typed( $d->type( 'array' ) )
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

    public function get_name() {
        return 'mixtape_casette';
    }

    protected function validate_author( $validation_data ) {
        $author_id = $validation_data->get_value();
        $author = $this->get_author( $author_id );
        if ( null === $author ) {
            return new WP_Error( 'invalid-author-id', __( 'Invalid author id', 'casette' ) );
        }
        return true;
    }

    protected function validate_status( $validation_data ) {
        $model = $validation_data->get_model();
        $status = $validation_data->get_value();
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

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function permissions_check($request, $action) {
        return true;
    }
}

class Song extends Mixtape_Model_Declaration {
    public function declare_fields($d ) {
        return array(
            $d->field()
                ->named( 'id' )
                ->map_from( 'ID' )
                ->typed('integer')
                ->description( 'Unique identifier for the object.' )
                ->sanitized_by( 'as_uint' ),
            $d->field()
                ->named( 'title' )
                ->map_from( 'post_title' )
                ->typed('string')
                ->description( 'The song title.' )
                ->required( true ),
        );
    }

    public function get_ratings() {
        return array();
    }

    public function get_name() {
        return 'mixtape_casette_song';
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
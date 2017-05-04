<?php

class DoingItWrongDelegate extends Mixtape_Model_Delegate {
}

class Casette extends Mixtape_Model_Delegate {
    public function declare_fields( $def ) {
            return array(
                $def->field()
                    ->with_name( 'id' )
                    ->map_from( 'ID' )
                    ->with_value_type('integer')
                    ->with_description( 'Unique identifier for the object.' )
                    ->with_before_return( 'as_uint' ),
                $def->field()
                    ->with_name( 'title' )
                    ->map_from( 'post_title' )
                    ->with_value_type('string')
                    ->with_description( 'The casette title.' )
                    ->required( true ),
                $def->field()
                    ->with_name( 'author' )
                    ->map_from( 'post_author' )
                    ->with_value_type('integer')
                    ->with_validations( 'validate_author' )
                    ->with_description( __( 'The author identifier.', 'casette' ) )
                    ->with_default_value( 0 )
                    ->with_data_transfer_name( 'authorID' )
                    ->with_before_return( 'as_uint' ),
                $def->field()
                    ->with_name( 'status' )
                    ->with_value_type('string')
                    ->with_validations( 'validate_status' )
                    ->with_default_value('draft')
                    ->with_description( 'The casette status.' )
                    ->map_from( 'post_status' ),

                $def->derived_field()
                    ->with_name( 'ratings' )
                    ->map_from( 'get_ratings' )
                    ->with_description( 'The casette ratings' )
                    ->with_data_transfer_name( 'the_ratings' ),

                $def->meta_field()
                    ->with_name( 'songs' )
                    ->map_from( '_casette_song_ids' )
                    ->with_value_type( 'array' )
                    ->with_default_value( null )
                    ->with_description( 'The casette songs' )
                    ->with_data_transfer_name( 'song_ids' ),
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
}

class Song extends Mixtape_Model_Delegate {
    public function declare_fields( $def ) {
        return array(
            $def->field()
                ->with_name( 'id' )
                ->map_from( 'ID' )
                ->with_value_type('integer')
                ->with_description( 'Unique identifier for the object.' )
                ->with_before_return( 'as_uint' ),
            $def->field()
                ->with_name( 'title' )
                ->map_from( 'post_title' )
                ->with_value_type('string')
                ->with_description( 'The song title.' )
                ->required( true ),
        );
    }

    public function get_ratings() {
        return array();
    }
}
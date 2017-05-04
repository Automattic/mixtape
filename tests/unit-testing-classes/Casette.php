<?php

class DoingItWrongModel extends Mixtape_Model {
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

                $def->derived_field()
                    ->with_name( 'ratings' )
                    ->map_from( 'get_ratings' )
                    ->with_description( 'The casette ratings' )
                    ->with_json_name( 'the_ratings' ),

                $def->meta_field()
                    ->with_name( 'songs' )
                    ->map_from( '_casette_song_ids' )
                    ->with_description( 'The casette songs' )
                    ->with_json_name( 'song_ids' ),
            );
    }

    public function get_ratings() {
        return array();
    }

    public function get_id( $model ) {
        return $model->get( 'id' );
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

    public function get_id( $model ) {
        return $model->get( 'id' );
    }
}
<?php

class Casette extends MT_Model {
	/**
	 * Declare our fields
	 *
	 * @return array
	 */
	public function declare_fields() {
		$d = $this->get_environment();
		return array(
			$d->field( 'id' )
				->with_map_from( 'ID' )
				->with_type( $d->type( 'uint' ) )
				->with_description( 'Unique identifier for the object.' ),

			$d->field( 'title', 'The casette title.' )
				->with_map_from( 'post_title' )
				->with_type( $d->type( 'string' ) )
				->with_required(),

			$d->field( 'author', __( 'The author identifier.', 'casette' ) )
				->with_map_from( 'post_author' )
				->with_type( $d->type( 'uint' ) )
				->with_validations( 'validate_author' )
				->with_default( 0 )
				->with_dto_name( 'authorID' ),

			$d->field( 'status', 'The casette status.' )
				->with_type( $d->type( 'string' ) )
				->with_validations( 'validate_status' )
				->with_default( 'draft' )
				->with_map_from( 'post_status' ),

			$d->field( 'ratings', 'The casette ratings' )
				->derived( 'get_ratings' )
				->with_dto_name( 'the_ratings' ),

			$d->field( 'songs', 'The casette songs', 'meta' )
				->with_map_from( '_casette_song_ids' )
				->with_type( $d->type( 'array' ) )
				->with_deserializer( 'song_before_return' )
				->with_serializer( 'song_before_save' )
				->with_dto_name( 'song_ids' ),
		);
	}

	/**
	 * Get our Ratings
	 *
	 * @return array
	 */
	public function get_ratings() {
		return array( 1 );
	}

	/**
	 * Our ID
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->get( 'id' );
	}

	/**
	 * Our Validation for Author.
	 *
	 * @param int $author_id The ID.
	 * @return bool|WP_Error
	 */
	protected function validate_author( $author_id ) {
		$author = $this->get_author( $author_id );
		if ( null === $author ) {
			return new WP_Error( 'invalid-author-id', __( 'Invalid author id', 'casette' ) );
		}
		return true;
	}

	/**
	 * Validate our Status
	 *
	 * @param string $status Status.
	 *
	 * @return bool|WP_Error
	 */
	protected function validate_status( $status ) {
		if ( 'publish' === $status ) {
			$author_id = $this->get( 'author' );
			if ( empty( $author_id ) || null === $this->get_author( $author_id ) ) {
				return new WP_Error( 'missing-author-id', __( 'Cannot publish when author is empty', 'casette' ) );
			}
		}

		return true;
	}

	/**
	 * Get Author
	 *
	 * @param int $author_id ID.
	 * @return false|WP_User
	 */
	private function get_author( $author_id ) {
		return get_user_by( 'id', $author_id );
	}

	/**
	 * Song Before Return Callback
	 *
	 * @param mixed $value Val.
	 * @return array
	 */
	function song_before_return( $value ) {
		return array_map( 'absint', explode( ',', $value ) );
	}

	/**
	 * Song before save
	 *
	 * @param array $value V.
	 * @return string
	 */
	function song_before_save( $value ) {
		return implode( ',', $value );
	}
}
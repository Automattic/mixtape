<?php
/**
 * Register our custom post types
 *
 * @package mt-example
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Casette_Post_Types
 */
class Casette_Post_Types {
	/**
	 * Register our types
	 */
	static function register() {
		register_post_type( 'mixtape_casette',
			array(
				'labels' => array(
					'name' => __( 'Casettes' ),
					'singular_name' => __( 'Casette' ),
				),
				'public' => true,
				'has_archive' => true,
			)
		);
		register_post_type( 'mixtape_casette_song',
			array(
				'labels' => array(
					'name' => __( 'Casette Songs' ),
					'singular_name' => __( 'Casette Song' ),
				),
				'public' => true,
				'has_archive' => true,
			)
		);
	}
}

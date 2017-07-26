<?php
/**
 * Casette Admin Settings
 *
 * @package MT/Example
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Casette_Admin_Settings
 */
class Casette_Admin_Settings {
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
						'attributes'  => array(),
					),
					array(
						'name'       => 'mixtape_casette_hide_listened',
						'std'        => '0',
						'label'      => __( 'Hide Listened Casettes', 'mixtape' ),
						'cb_label'   => __( 'Hide Listened Casettes', 'mixtape' ),
						'desc'       => __( 'If enabled, listened Casettes will be hidden from archives.', 'mixtape' ),
						'type'       => 'checkbox',
						'attributes' => array(),
					),
					array(
						'name'       => 'mixtape_casette_enable_private',
						'std'        => '0',
						'label'      => __( 'Users can create private Casettes', 'mixtape' ),
						'cb_label'   => __( 'Users can create private Casettes', 'mixtape' ),
						'desc'       => __( 'If enabled, Users can create private Casettes (defaults to false)', 'mixtape' ),
						'type'       => 'checkbox',
						'attributes' => array(),
					),
				),
			),
		);
	}
}
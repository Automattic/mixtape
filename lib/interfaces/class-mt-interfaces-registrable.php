<?php
/**
 * Something that can be registered with an environment
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface MT_Interfaces_Registrable
 */

interface MT_Interfaces_Registrable {
	/**
	 * Register This with an environment
	 *
	 * @param MT_Environment $environment The Environment to use.
	 * @return void
	 */
	function register( $environment );
}

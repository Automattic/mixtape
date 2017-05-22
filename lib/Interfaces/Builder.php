<?php
/**
 * Build Stuff
 *
 * @package Mixtape
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface Mixtape_Interfaces_Builder
 */
interface Mixtape_Interfaces_Builder {
	/**
	 * Build something
	 *
	 * @return mixed
	 */
	function build();
}

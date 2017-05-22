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
 * Interface MT_Interfaces_Builder
 */
interface MT_Interfaces_Builder {
	/**
	 * Build something
	 *
	 * @return mixed
	 */
	function build();
}

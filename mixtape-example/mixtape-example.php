<?php
/**
 * Plugin Name: Mixtape Example
 * Plugin URI: https://Automattic.com/
 * Description: An example of using mixtape
 * Version: 0.1.0
 * Author: Automattic
 * Author URI: https://Automattic.com/
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least: 4.7
 * Tested up to: 4.7.4
 * Text Domain: mixtape-example
 * Domain path: /lang/
 *
 * @package mt-example
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Run our plugin
 */
function run_plugin() {
	$base_path = dirname( __FILE__ );
	include_once path_join( $base_path, 'class-casette-post-types.php' );
	Casette_Post_Types::register();

	$mixtape_path = dirname( $base_path );
	include_once( path_join( $mixtape_path, 'loader.php' ) );
	$mixtape = MT_Bootstrap::create()->load();
	include_once path_join( $base_path, 'casette.php' );

	Casette_Api::register( $mixtape );
}

add_action( 'init', 'run_plugin' );


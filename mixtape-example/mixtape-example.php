<?php
/*
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
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function run_plugin() {
    add_action('init', function () {
        $base_path = dirname( __FILE__ );
        include_once ( path_join( $base_path, 'CasettePostTypes.php' ) );
        CasettePostTypes::register();

        $mixtape_path = dirname( $base_path );
        include_once ( path_join( $mixtape_path, 'loader.php' ) );
        $mixtape = Mixtape::create()->load(); //load it before defining our classes
        include_once ( path_join( $base_path, 'Casette.php' ) );

        $mixtape
            ->environment()
            ->define_model( new Casette(), new Mixtape_Data_Store_Cpt( 'mixtape_casette' ) )
            ->add_rest_bundle( new CasetteApiBundleV1() )
            ->start();

    });
}

run_plugin();


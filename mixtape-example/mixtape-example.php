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
        $generated_path = path_join( $base_path, 'inc' );
        include_once ( path_join( $mixtape_path, 'loader.php' ) );
        $mixtape = MT_Bootstrap::create()->load(); //load it before defining our classes
        include_once ( path_join( $base_path, 'Casette.php' ) );

        $env = $mixtape->environment();
        $env->define()->model(
            'Casette'
        )->with_data_store(
            $env->define()->data_store()
                ->custom_post_type()
                ->with_post_type('mixtape_casette')
        );
        $bundle = $env
            ->define()->rest_api('mixtape-example/v1');
        $bundle->endpoint()
            ->crud( '/casettes' )
            ->for_model( $env->get()->model( 'Casette' ) );
        $bundle->endpoint()
            ->with_class( CasetteApiEndpointVersion::class );

        $mixtape->environment()->start();

    });
}

run_plugin();


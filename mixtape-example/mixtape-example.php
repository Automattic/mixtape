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
        $mixtape = Mixtape::create( array(
//            'prefix' => 'Mixtape',
//            'base_dir' => untrailingslashit( dirname( __FILE__ ) ),
//            'prefix_dir' => $generated_path,
//            'is_debugging' => false,
        ) )->load(); //load it before defining our classes
        include_once ( path_join( $base_path, 'Casette.php' ) );

        $env = $mixtape->environment();
        $env->define_model( new Casette(), new Mixtape_Data_Store_CustomPostType( 'mixtape_casette' ) );
        $bundle = $env
            ->define_bundle('mixtape-example/v1')
            ->add_endpoint( $env->crud( $env->model_definition( 'Casette' ), '/casettes' ) )
            ->add_endpoint( $env->endpoint( CasetteApiEndpointVersion::class ) );
        $env->add_rest_bundle( $bundle );

        $mixtape->environment()->start();

    });
}

run_plugin();


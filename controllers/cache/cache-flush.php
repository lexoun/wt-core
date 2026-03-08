<?php

$site = $_REQUEST['site'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$showLog = false;

define('BASE_PATH', '//home/html/wellnesstrade.savana-hosting.cz/public_html/'.$site.'.cz');

// Load WordPress.
require( BASE_PATH.'/wp-load.php' );

define( 'WP_USE_THEMES', false );

// Add one page/post per line.
$pages_to_clean_preload = [
    'https://www.'.$site.'.cz/kontakty',//copy this line as many times as necessary.
];

if ( function_exists( 'rocket_clean_post' ) ) {

    foreach( $pages_to_clean_preload as $page_to_clean) {
        rocket_clean_post( url_to_postid ( $page_to_clean ) );
    }
}

if ( function_exists( 'get_rocket_option' ) ) {

    if( 1 == get_rocket_option( 'manual_preload' ) ) {

        $args = array();

        if( 1 == get_rocket_option( 'cache_webp' ) ) {
            $args[ 'headers' ][ 'Accept' ]      	= 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
            $args[ 'headers' ][ 'HTTP_ACCEPT' ] 	= 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
        }

        // Preload desktop pages/posts.
        rocket_preload_page( $pages_to_clean_preload, $args );

        if( 1 == get_rocket_option( 'do_caching_mobile_files' ) ) {
            $args[ 'headers' ][ 'user-agent' ] 	= 'Mozilla/5.0 (Linux; Android 8.0.0;) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Mobile Safari/537.36';

            // Preload mobile pages/posts.
            rocket_preload_page(  $pages_to_clean_preload, $args );
        }
    }
}

function rocket_preload_page ( $pages_to_preload, $args ){

    foreach( $pages_to_preload as $page_to_preload ) {
        wp_remote_get( esc_url_raw ( $page_to_preload ), $args );
    }
}
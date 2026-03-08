<?php

define( 'COOKIE_DOMAIN', false );
define( 'DISABLE_WP_CRON', true );
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', true );

define( 'BASE_PATH', '/mnt/data/accounts/w/wellnesstrade/data/www/'.$shop['folder'].'.cz');

require_once BASE_PATH . '/wp-config.php';

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//require_once ABSPATH . '/wp-load.php';

// Load the L10n library.
require_once ABSPATH . WPINC . '/l10n.php';
require_once ABSPATH . WPINC . '/class-wp-locale.php';
require_once ABSPATH . WPINC . '/class-wp-locale-switcher.php';

// Run the installer if WordPress is not installed.
//wp_not_installed();

// Load most of WordPress.
require ABSPATH . WPINC . '/class-wp-walker.php';
//require ABSPATH . WPINC . '/class-wp-ajax-response.php';
require ABSPATH . WPINC . '/capabilities.php';
//require ABSPATH . WPINC . '/class-wp-roles.php';
//require ABSPATH . WPINC . '/class-wp-role.php';
require ABSPATH . WPINC . '/class-wp-user.php';
require ABSPATH . WPINC . '/class-wp-query.php';
require ABSPATH . WPINC . '/query.php';
//require ABSPATH . WPINC . '/class-wp-date-query.php';
require ABSPATH . WPINC . '/theme.php';
//require ABSPATH . WPINC . '/class-wp-theme.php';
//require ABSPATH . WPINC . '/template.php';
//require ABSPATH . WPINC . '/class-wp-user-request.php';
require ABSPATH . WPINC . '/user.php';
//require ABSPATH . WPINC . '/class-wp-user-query.php';
//require ABSPATH . WPINC . '/class-wp-session-tokens.php';
//require ABSPATH . WPINC . '/class-wp-user-meta-session-tokens.php';
require ABSPATH . WPINC . '/class-wp-metadata-lazyloader.php';
require ABSPATH . WPINC . '/general-template.php';
require ABSPATH . WPINC . '/link-template.php';
require ABSPATH . WPINC . '/author-template.php';
require ABSPATH . WPINC . '/post.php';
//require ABSPATH . WPINC . '/class-walker-page.php';
//require ABSPATH . WPINC . '/class-walker-page-dropdown.php';
require ABSPATH . WPINC . '/class-wp-post-type.php';
require ABSPATH . WPINC . '/class-wp-post.php';
require ABSPATH . WPINC . '/post-template.php';
require ABSPATH . WPINC . '/revision.php';
require ABSPATH . WPINC . '/post-formats.php';
require ABSPATH . WPINC . '/post-thumbnail-template.php';
require ABSPATH . WPINC . '/category.php';
//require ABSPATH . WPINC . '/class-walker-category.php';
//require ABSPATH . WPINC . '/class-walker-category-dropdown.php';
require ABSPATH . WPINC . '/category-template.php';
require ABSPATH . WPINC . '/comment.php';
//require ABSPATH . WPINC . '/class-wp-comment.php';
//require ABSPATH . WPINC . '/class-wp-comment-query.php';
//require ABSPATH . WPINC . '/class-walker-comment.php';
//require ABSPATH . WPINC . '/comment-template.php';
require ABSPATH . WPINC . '/rewrite.php';
require ABSPATH . WPINC . '/class-wp-rewrite.php';
//require ABSPATH . WPINC . '/feed.php';
//require ABSPATH . WPINC . '/bookmark.php';
//require ABSPATH . WPINC . '/bookmark-template.php';
require ABSPATH . WPINC . '/kses.php';
require ABSPATH . WPINC . '/cron.php';
//require ABSPATH . WPINC . '/deprecated.php';
require ABSPATH . WPINC . '/script-loader.php';

require ABSPATH . WPINC . '/class-wp-theme-json-resolver.php';
require ABSPATH . WPINC . '/taxonomy.php';
require ABSPATH . WPINC . '/class-wp-taxonomy.php';
require ABSPATH . WPINC . '/class-wp-term.php';
require ABSPATH . WPINC . '/class-wp-term-query.php';
require ABSPATH . WPINC . '/class-wp-tax-query.php';
//require ABSPATH . WPINC . '/update.php';
//require ABSPATH . WPINC . '/canonical.php';
require ABSPATH . WPINC . '/shortcodes.php';
require ABSPATH . WPINC . '/embed.php';
require ABSPATH . WPINC . '/class-wp-embed.php';
//require ABSPATH . WPINC . '/class-wp-oembed.php';
//require ABSPATH . WPINC . '/class-wp-oembed-controller.php';
require ABSPATH . WPINC . '/media.php';
require ABSPATH . WPINC . '/http.php';
require ABSPATH . WPINC . '/class-http.php';
//require ABSPATH . WPINC . '/class-wp-http-streams.php';
//require ABSPATH . WPINC . '/class-wp-http-curl.php';
require ABSPATH . WPINC . '/class-wp-http-proxy.php';
require ABSPATH . WPINC . '/class-wp-http-cookie.php';
require ABSPATH . WPINC . '/class-wp-http-encoding.php';
require ABSPATH . WPINC . '/class-wp-http-response.php';
require ABSPATH . WPINC . '/class-wp-http-requests-response.php';
require ABSPATH . WPINC . '/class-wp-http-requests-hooks.php';
require ABSPATH . WPINC . '/widgets.php';
require ABSPATH . WPINC . '/class-wp-widget.php';
require ABSPATH . WPINC . '/class-wp-widget-factory.php';
require ABSPATH . WPINC . '/nav-menu.php';
//require ABSPATH . WPINC . '/nav-menu-template.php';
//require ABSPATH . WPINC . '/admin-bar.php';
require ABSPATH . WPINC . '/rest-api.php';
require ABSPATH . WPINC . '/class-wp-block-type.php';
require ABSPATH . WPINC . '/class-wp-block-styles-registry.php';
require ABSPATH . WPINC . '/class-wp-block-type-registry.php';
require ABSPATH . WPINC . '/class-wp-block-parser.php';
require ABSPATH . WPINC . '/blocks.php';
require ABSPATH . WPINC . '/blocks/block.php';

$GLOBALS['wp_embed'] = new WP_Embed();

wp_plugin_directory_constants();

$GLOBALS['wp_plugin_paths'] = array();

// Define constants after multisite is loaded.
wp_cookie_constants();

$plugin = BASE_PATH . '/wp-content/plugins/woocommerce/woocommerce.php';

wp_register_plugin_realpath( $plugin );
include_once $plugin;
do_action( 'plugin_loaded', $plugin );
unset( $plugin );

// Load pluggable functions.
require ABSPATH . WPINC . '/pluggable.php';
require ABSPATH . WPINC . '/pluggable-deprecated.php';

// Set internal encoding.
wp_set_internal_encoding();

// Run wp_cache_postload() if object cache is enabled and the function exists.
if ( WP_CACHE && function_exists( 'wp_cache_postload' ) ) {
    wp_cache_postload();
}

/**
 * Fires once activated plugins have loaded.
 *
 * Pluggable functions are also available at this point in the loading order.
 *
 * @since 1.5.0
 */
do_action( 'plugins_loaded' );

// Define constants which affect functionality if not already defined.
wp_functionality_constants();

/**
 * Holds the WordPress Rewrite object for creating pretty URLs
 *
 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
 * @since 1.5.0
 */
$GLOBALS['wp_rewrite'] = new WP_Rewrite();

/**
 * WordPress Object
 *
 * @global WP $wp Current WordPress environment instance.
 * @since 2.0.0
 */
$GLOBALS['wp'] = new WP();

/**
 * WordPress Widget Factory Object
 *
 * @global WP_Widget_Factory $wp_widget_factory
 * @since 2.8.0
 */
$GLOBALS['wp_widget_factory'] = new WP_Widget_Factory();

// Define the template related constants.
wp_templating_constants();

/**
 * WordPress Locale object for loading locale domain date and various strings.
 *
 * @global WP_Locale $wp_locale WordPress date and time locale object.
 * @since 2.1.0
 */
$GLOBALS['wp_locale'] = new WP_Locale();

/**
 * Fires after WordPress has finished loading but before any headers are sent.
 *
 * Most of WP is loaded at this stage, and the user is authenticated. WP continues
 * to load on the {@see 'init'} hook that follows (e.g. widgets), and many plugins instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once WP is loaded, use the {@see 'wp_loaded'} hook below.
 *
 * @since 1.5.0
 */
do_action( 'init' );
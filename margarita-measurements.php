<?php
/**
 * Plugin Name: Margarita Measurements
 * Plugin URI: #
 * Description: Calculate perfect margarita ratios with presets, units, and ABV estimate. Shortcode: [margarita_measurements]. Also provides a Gutenberg block.
 * Version: 2.4.0
 * Author: Bryan Chetcuti (https://github.com/bchetcuti/margaritaWP)
 * Text Domain: margarita-measurements
 * Requires at least: 5.8
 * Requires PHP: 8.1
 *
 * @package MargaritaMeasurements
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MM_VERSION', '2.4.0' );
define( 'MM_PLUGIN_FILE', __FILE__ );
define( 'MM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( function( $class ) {
    if ( strpos( $class, 'MM_' ) === 0 ) {
        $file = MM_PLUGIN_DIR . 'includes/class-' . strtolower( str_replace( 'MM_', '', $class ) ) . '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
} );

register_activation_hook( __FILE__, function() {
    $defaults = array(
        'unit'           => 'ml',
        'default_preset' => 'classic',
        'max_drinks'     => 25,
        'show_abv'       => 1,
    );
    foreach ( $defaults as $k => $v ) {
        if ( get_option( "mm_$k", null ) === null ) {
            update_option( "mm_$k", $v );
        }
    }
} );

add_action( 'plugins_loaded', function() {
    MM_Plugin::instance();
    MM_Settings::instance();
    MM_REST::instance();
    MM_Ajax::instance();
} );

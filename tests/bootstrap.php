<?php
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}
// Minimal bootstrap placeholder for CI.
if ( ! function_exists( '__' ) ) { function __( $text, $domain = null ) { return $text; } }
if ( ! function_exists( 'sanitize_key' ) ) { function sanitize_key( $key ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) ); } }
if ( ! function_exists( 'get_option' ) ) { function get_option( $key, $default = false ) { return $default; } }
require_once dirname(__DIR__) . '/includes/class-calculator.php';

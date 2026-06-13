<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MM_REST {
    private static $instance = null;
    public static function instance() { if ( null === self::$instance ) { self::$instance = new self(); } return self::$instance; }
    private function __construct() { add_action( 'rest_api_init', array( $this, 'routes' ) ); }
    public function routes() {

        register_rest_route( 'margarita/v1', '/presets', array(
            'methods'  => 'GET',
            'permission_callback' => '__return_true',
            'callback' => array( $this, 'presets' ),
        ) );
        register_rest_route( 'margarita/v1', '/calculate', array(
            'methods'  => 'GET',
            'permission_callback' => '__return_true',
            'args'     => array(
                'preset'     => array( 'sanitize_callback' => 'sanitize_key' ),
                'drinks'     => array( 'sanitize_callback' => 'absint' ),
                'pitcher_ml' => array( 'sanitize_callback' => 'floatval' ),
                'mode'       => array( 'sanitize_callback' => 'sanitize_key' ),
                'unit'       => array( 'sanitize_callback' => 'sanitize_text_field' ),
                'wet_rim'    => array( 'sanitize_callback' => 'rest_sanitize_boolean' ),
                'flavour'    => array( 'sanitize_callback' => 'sanitize_key' ),
                'standard_region' => array( 'sanitize_callback' => 'sanitize_key' ),
                'tequila_abv' => array( 'sanitize_callback' => 'floatval' ),
                'triple_sec_abv' => array( 'sanitize_callback' => 'floatval' ),
            ),
            'callback' => array( $this, 'calc' ),
        ) );
    }

    public function presets( WP_REST_Request $request ) {
        $presets = MM_Plugin::instance()->calc->list_presets();
        $data    = array();

        foreach ( $presets as $key => $preset ) {
            $key = sanitize_key( $key );
            if ( '' === $key ) {
                continue;
            }
            $data[] = array(
                'key'   => $key,
                'label' => sanitize_text_field( $preset['label'] ?? ucfirst( $key ) ),
                'note'  => sanitize_text_field( $preset['note'] ?? '' ),
            );
        }

        return rest_ensure_response( $data );
    }
    public function calc( WP_REST_Request $request ) {
        $max  = (int) get_option( 'mm_max_drinks', 25 );
        $mode = sanitize_key( $request->get_param( 'mode' ) ?: 'drinks' );
        $args = array(
            'preset'     => $request->get_param( 'preset' ) ?: 'classic',
            'drinks'     => min( max( 1, (int) $request->get_param( 'drinks' ) ), $max ),
            'pitcher_ml' => min( 5000, max( 100, (float) ( $request->get_param( 'pitcher_ml' ) ?: 1000 ) ) ),
            'unit'       => $request->get_param( 'unit' ) ?: get_option( 'mm_unit', 'ml' ),
            'flavour'    => $request->get_param( 'flavour' ) ?: 'none',
            'standard_region' => $request->get_param( 'standard_region' ) ?: 'AU',
            'tequila_abv' => $request->get_param( 'tequila_abv' ) ?: 40,
            'triple_sec_abv' => $request->get_param( 'triple_sec_abv' ) ?: 40,
            'wet_rim'    => null === $request->get_param( 'wet_rim' ) ? true : (bool) $request->get_param( 'wet_rim' ),
        );
        $calc            = MM_Plugin::instance()->calc;
        $allowed_presets = array_keys( $calc->list_presets() );
        $allowed_units   = array( 'ml', 'oz', 'shot', 'nip' );
        $args['preset']  = in_array( $args['preset'], $allowed_presets, true ) ? $args['preset'] : 'classic';
        $args['unit']    = in_array( $args['unit'], $allowed_units, true ) ? $args['unit'] : get_option( 'mm_unit', 'ml' );
        $args['flavour'] = $calc->normalise_flavour_key( $args['flavour'] );
        $args['standard_region'] = $calc->normalise_standard_drink_region( $args['standard_region'] );
        $args['tequila_abv'] = $calc->sanitize_tequila_abv( $args['tequila_abv'] );
        $args['triple_sec_abv'] = $calc->sanitize_triple_sec_abv( $args['triple_sec_abv'] );
        $mode            = in_array( $mode, array( 'drinks', 'pitcher' ), true ) ? $mode : 'drinks';
        $data            = 'pitcher' === $mode ? $calc->pitcher( $args ) : $calc->batch( $args );
        foreach ( $data['quantities'] as &$v ) {
            if ( is_array( $v ) && isset( $v['display'] ) ) { $v['display'] = round( $v['display'], 2 ); }
        }
        unset( $v );
        $data['abv'] = round( $data['abv'], 1 );
        return rest_ensure_response( $data );
    }
}

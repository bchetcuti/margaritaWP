<?php
class MM_REST {
    private static $instance = null;
    public static function instance() { if ( null === self::$instance ) { self::$instance = new self(); } return self::$instance; }
    private function __construct() { add_action( 'rest_api_init', array( $this, 'routes' ) ); }
    public function routes() {
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
            ),
            'callback' => array( $this, 'calc' ),
        ) );
    }
    public function calc( WP_REST_Request $request ) {
        $max  = (int) get_option( 'mm_max_drinks', 25 );
        $mode = sanitize_key( $request->get_param( 'mode' ) ?: 'drinks' );
        $args = array(
            'preset'     => $request->get_param( 'preset' ) ?: 'classic',
            'drinks'     => min( max( 1, (int) $request->get_param( 'drinks' ) ), $max ),
            'pitcher_ml' => min( 5000, max( 100, (float) ( $request->get_param( 'pitcher_ml' ) ?: 1000 ) ) ),
            'unit'       => $request->get_param( 'unit' ) ?: get_option( 'mm_unit', 'ml' ),
            'wet_rim'    => null === $request->get_param( 'wet_rim' ) ? true : (bool) $request->get_param( 'wet_rim' ),
        );
        $calc            = MM_Plugin::instance()->calc;
        $allowed_presets = array_keys( $calc->list_presets() );
        $args['preset']  = in_array( $args['preset'], $allowed_presets, true ) ? $args['preset'] : 'classic';
        $data            = 'pitcher' === $mode ? $calc->pitcher( $args ) : $calc->batch( $args );
        foreach ( $data['quantities'] as &$v ) {
            if ( is_array( $v ) && isset( $v['display'] ) ) { $v['display'] = round( $v['display'], 2 ); }
        }
        unset( $v );
        $data['abv'] = round( $data['abv'], 1 );
        return rest_ensure_response( $data );
    }
}

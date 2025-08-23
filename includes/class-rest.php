<?php
class MM_REST {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'routes' ) );
    }

    public function routes() {
        register_rest_route( 'margarita/v1', '/calculate', array(
            'methods'  => 'GET',
            'permission_callback' => '__return_true',
            'args'     => array(
                'preset' => array( 'sanitize_callback' => 'sanitize_text_field' ),
                'drinks' => array( 'sanitize_callback' => 'absint' ),
                'unit'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
            ),
            'callback' => array( $this, 'calc' ),
        ) );
    }

    public function calc( WP_REST_Request $request ) {
        $args = array(
            'preset' => $request->get_param( 'preset' ) ?: 'classic',
            'drinks' => (int) $request->get_param( 'drinks' ) ?: 1,
            'unit'   => $request->get_param( 'unit' ) ?: get_option('mm_unit','ml'),
        );
        $calc = MM_Plugin::instance()->calc;
        $data = $calc->batch( $args );

        // Round display values for REST response also.
        foreach ( $data['quantities'] as $k => &$v ) {
            if ( is_array($v) && isset($v['display']) ) {
                $v['display'] = round( $v['display'], 2 );
            }
        }
        $data['abv'] = round( $data['abv'], 1 );

        return rest_ensure_response( $data );
    }
}

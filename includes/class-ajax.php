<?php
class MM_Ajax {
    private static $instance = null;
    public static function instance() { if ( null === self::$instance ) { self::$instance = new self(); } return self::$instance; }
    private function __construct() {
        add_action( 'wp_ajax_mm_calculate', array( $this, 'handle' ) );
        add_action( 'wp_ajax_nopriv_mm_calculate', array( $this, 'handle' ) );
    }
    public function handle() {
        check_ajax_referer( 'mm_nonce', 'nonce' );
        $args = array(
            'preset' => sanitize_text_field( $_POST['preset'] ?? 'classic' ),
            'drinks' => absint( $_POST['drinks'] ?? 1 ),
            'unit'   => sanitize_text_field( $_POST['unit'] ?? get_option('mm_unit','ml') ),
        );
        $calc = MM_Plugin::instance()->calc;
        $data = $calc->batch( $args );
        foreach ( $data['quantities'] as $k => &$v ) {
            if ( is_array($v) && isset($v['display']) ) { $v['display'] = round( $v['display'], 2 ); }
        }
        $data['abv'] = round( $data['abv'], 1 );
        wp_send_json_success( $data );
    }
}

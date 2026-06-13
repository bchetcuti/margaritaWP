<?php
class MM_Ajax {
    private static $instance = null;
    public static function instance() { if ( null === self::$instance ) { self::$instance = new self(); } return self::$instance; }
    private function __construct() {
        add_action( 'wp_ajax_mm_calculate', array( $this, 'handle' ) );
        add_action( 'wp_ajax_nopriv_mm_calculate', array( $this, 'handle' ) );
        add_action( 'wp_ajax_mm_delete_preset', array( $this, 'delete_preset' ) );
    }
    public function handle() {
        check_ajax_referer( 'mm_nonce', 'nonce' );
        $max  = (int) get_option( 'mm_max_drinks', 25 );
        $mode = sanitize_key( $_POST['mode'] ?? 'drinks' );
        $args = array(
            'preset'     => sanitize_key( $_POST['preset'] ?? 'classic' ),
            'drinks'     => min( max( 1, absint( $_POST['drinks'] ?? 1 ) ), $max ),
            'pitcher_ml' => min( 5000, max( 100, (float) ( $_POST['pitcher_ml'] ?? 1000 ) ) ),
            'unit'       => sanitize_text_field( $_POST['unit'] ?? get_option( 'mm_unit', 'ml' ) ),
            'wet_rim'    => ! empty( $_POST['wet_rim'] ),
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
        wp_send_json_success( $data );
    }
    public function delete_preset() {
        check_ajax_referer( 'mm_delete_preset_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'margarita-measurements' ) ), 403 );
        }
        $key     = sanitize_key( $_POST['preset_key'] ?? '' );
        $custom  = get_option( 'mm_custom_presets', array() );
        $custom  = is_array( $custom ) ? $custom : array();
        if ( isset( $custom[ $key ] ) ) {
            unset( $custom[ $key ] );
            update_option( 'mm_custom_presets', $custom );
        }
        wp_send_json_success();
    }
}

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
        $mode = sanitize_key( wp_unslash( $_POST['mode'] ?? 'drinks' ) );
        $args = array(
            'preset'     => sanitize_key( wp_unslash( $_POST['preset'] ?? 'classic' ) ),
            'drinks'     => min( max( 1, absint( wp_unslash( $_POST['drinks'] ?? 1 ) ) ), $max ),
            'pitcher_ml' => min( 5000, max( 100, (float) wp_unslash( $_POST['pitcher_ml'] ?? 1000 ) ) ),
            'unit'       => sanitize_key( wp_unslash( $_POST['unit'] ?? get_option( 'mm_unit', 'ml' ) ) ),
            'flavour'    => sanitize_key( wp_unslash( $_POST['flavour'] ?? 'none' ) ),
            'wet_rim'    => ! empty( $_POST['wet_rim'] ),
        );
        $calc            = MM_Plugin::instance()->calc;
        $allowed_presets = array_keys( $calc->list_presets() );
        $allowed_units   = array( 'ml', 'oz', 'shot', 'nip' );
        $args['preset']  = in_array( $args['preset'], $allowed_presets, true ) ? $args['preset'] : 'classic';
        $args['unit']    = in_array( $args['unit'], $allowed_units, true ) ? $args['unit'] : get_option( 'mm_unit', 'ml' );
        $args['flavour'] = $calc->normalise_flavour_key( $args['flavour'] );
        $mode            = in_array( $mode, array( 'drinks', 'pitcher' ), true ) ? $mode : 'drinks';
        $data            = 'pitcher' === $mode ? $calc->pitcher( $args ) : $calc->batch( $args );
        foreach ( $data['quantities'] as &$v ) {
            if ( is_array( $v ) && isset( $v['display'] ) ) { $v['display'] = round( $v['display'], 2 ); }
        }
        unset( $v );
        $data['abv'] = round( $data['abv'], 1 );
        if ( empty( $_POST['show_abv'] ) || ! empty( $data['flavour']['no_alcohol'] ) ) {
            unset( $data['abv'] );
        }
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

<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$options = array(
    'mm_unit',
    'mm_default_preset',
    'mm_max_drinks',
    'mm_show_abv',
    'mm_standard_drink_region',
    'mm_bottle_sizes',
    'mm_custom_presets',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
$options = array( 'unit', 'default_preset', 'max_drinks', 'show_abv' );
foreach ( $options as $key ) {
    delete_option( "mm_{$key}" );
}

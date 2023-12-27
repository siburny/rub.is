<?php

add_filter( 'admin_footer', 'dark_mode_dashboard_css_custom');

function dark_mode_dashboard_css_custom( $style ) {
    wp_register_style( 'dark-mode-dashboard-custom', '/wp-content/themes/dark-mode-dashboard-custom.css' );
    wp_enqueue_style( 'dark-mode-dashboard-custom' );
    
    return $style;
}
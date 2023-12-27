<?php

add_filter( 'dark_mode_dashboard_css', 'dark_mode_dashboard_css_custom');
add_filter( 'the_content', 'dark_mode_dashboard_css_custom');

function dark_mode_dashboard_css_custom( $style ) {
    wp_register_style( 'dark-mode-dashboard-custom', '/wp-content/themes/dark-mode-dashboard-custom.css' );
    wp_enqueue_style( 'dark-mode-dashboard-custom' );
    
    return $style;
}
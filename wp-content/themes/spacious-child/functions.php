<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'spacious-style', get_template_directory_uri() . '/style.css' );
}

// Add Shortcode
function please_note_sc($atts , $content = null) {
    return "<div style='margin-bottom:20px;padding:10px;border: 1px solid #dad55e;background: #fffa90;/*color: #777620;*/'>".$content."</div>";
}
add_shortcode( 'note', 'please_note_sc' );

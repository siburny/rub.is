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

function my_theme_before_sidebar(){
	$key_1_value = get_post_meta( get_the_ID(), 'amazon', true );
	if ( ! empty( $key_1_value ) ) {
?>
<script type="text/javascript">
amzn_assoc_placement = "adunit0";
amzn_assoc_search_bar = "false";
amzn_assoc_tracking_id = "rubis09-20";
amzn_assoc_ad_mode = "manual";
amzn_assoc_ad_type = "smart";
amzn_assoc_marketplace = "amazon";
amzn_assoc_region = "US";
amzn_assoc_title = "";
amzn_assoc_linkid = "bcae2949b17be18b4be41f3cccaa09d6";
amzn_assoc_asins = "<?php echo $key_1_value; ?>";
amzn_assoc_size = "x1200";
</script>
<?php
	}

}
add_action( 'spacious_before_sidebar', 'my_theme_before_sidebar' );

<?php



/*  ----------------------------------------------------------------------------
	TDS LOCKERS
*/
// add post meta for default locker
td_demo_content::add_locker_meta( array(
        'tds_locker_id' => (int) get_option( 'tds_default_locker_id' ),
        'tds_locker_meta' => array(
            'tds_locker_settings' => array(
                'tds_title' => 'Content available exclusively for subscribers ',
                'tds_message' => 'Enter your email address in the form bellow to get access.',
                'tds_input_placeholder' => 'Your email address',
                'tds_submit_btn_text' => 'Subscribe',
                'tds_after_btn_text' => 'Your email address is 100% safe from spam!',
                'tds_pp_msg' => 'I\'ve read and accept the <a href=\"#\">Privacy Policy</a>',
            ),
            'tds_locker_styles' => array(
                'tds_bg_color' => '#333237',
                'tds_title_color' => '#ffffff',
                'tds_message_color' => '#bbbbbb',
                'tds_input_bg_color' => '#ffffff',
                'tds_input_border_color' => '#ffffff',
                'tds_submit_btn_bg_color' => '#10bf6b',
                'tds_submit_btn_bg_color_h' => '#000000',
                'tds_pp_checked_color' => '#000000',
                'tds_pp_check_bg' => 'rgba(255,255,255,0.6)',
                'tds_pp_check_border_color' => 'rgba(16,191,107,0)',
                'tds_pp_msg_color' => '#a0a0a0',
                'tds_pp_msg_links_color' => '#10bf6b',
                'tds_pp_msg_links_color_h' => '#ffffff',
                'tds_general_font_family' => '420',
                'tds_title_font_size' => '28',
                'tds_title_font_line_height' => '1.3',
                'tds_title_font_weight' => '700',
                'tds_message_font_size' => '13',
                'tds_message_font_line_height' => '1.5',
                'tds_input_font_size' => '13',
                'tds_submit_btn_text_font_size' => '13',
            ),
        )
    )
);



/*  ----------------------------------------------------------------------------
	CLOUD TEMPLATES
*/
$template_header_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Header Template',
    'file' => 'header_cloud_template.txt',
    'template_type' => 'header',
));
td_demo_misc::update_global_header_template( 'tdb_template_' . $template_header_template_id);
update_post_meta( $template_header_template_id, 'header_mobile_menu_id', $menu_td_demo_header_menu_id);


$template_footer_template_global_id = td_demo_content::add_cloud_template(array(
    'title' => 'Footer Template – Global',
    'file' => 'footer_global_cloud_template.txt',
    'template_type' => 'footer',
));
td_demo_misc::update_global_footer_template( 'tdb_template_' . $template_footer_template_global_id);


$template_footer_template_home_id = td_demo_content::add_cloud_template(array(
    'title' => 'Footer Template - Home',
    'file' => 'footer_home_cloud_template.txt',
    'template_type' => 'footer',
));


$template_category_template_global_id = td_demo_content::add_cloud_template(array(
    'title' => 'Category Template - Global',
    'file' => 'cat_global_cloud_template.txt',
    'template_type' => 'category',
));
td_demo_misc::update_global_category_template( 'tdb_template_' . $template_category_template_global_id);


$template_category_template_guides_id = td_demo_content::add_cloud_template(array(
    'title' => 'Category Template - Guides',
    'file' => 'cat_guides_cloud_template.txt',
    'template_type' => 'category',
));


$template_single_template_global_id = td_demo_content::add_cloud_template(array(
    'title' => 'Single Template - Global',
    'file' => 'post_global_cloud_template.txt',
    'template_type' => 'single',
));
td_util::update_option('td_default_site_post_template', 'tdb_template_' . $template_single_template_global_id);


$template_single_template_guides_id = td_demo_content::add_cloud_template(array(
    'title' => 'Single Template - Guides',
    'file' => 'post_guides_cloud_template.txt',
    'template_type' => 'single',
));


$template_tag_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Tag Template',
    'file' => 'tag_cloud_template.txt',
    'template_type' => 'tag',
));
td_demo_misc::update_global_tag_template( 'tdb_template_' . $template_tag_template_id);


$template_search_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Search Template',
    'file' => 'search_cloud_template.txt',
    'template_type' => 'search',
));
td_demo_misc::update_global_search_template( 'tdb_template_' . $template_search_template_id);


$template_date_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Date Template',
    'file' => 'date_cloud_template.txt',
    'template_type' => 'date',
));
td_demo_misc::update_global_date_template( 'tdb_template_' . $template_date_template_id);


$template_author_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Author Template',
    'file' => 'author_cloud_template.txt',
    'template_type' => 'author',
));
td_demo_misc::update_global_author_template( 'tdb_template_' . $template_author_template_id);


$template_404_template_id = td_demo_content::add_cloud_template(array(
    'title' => '404 Template',
    'file' => '404_cloud_template.txt',
    'template_type' => '404',
));
td_demo_misc::update_global_404_template( 'tdb_template_' . $template_404_template_id);

/*  ----------------------------------------------------------------------------
	ATTACHMENTS
*/


/*  ----------------------------------------------------------------------------
	PAGES
*/
$page_home_id = td_demo_content::add_page(array(
    'title' => 'Home',
    'file' => 'home.txt',
    'homepage' => true,
    'footer_template_id' => $template_footer_template_home_id
));



/*  ----------------------------------------------------------------------------
	GENERAL SETTINGS
*/
td_demo_misc::update_background('', false);

td_demo_misc::update_background_mobile('');

td_demo_misc::update_background_login('');

td_demo_misc::update_background_header('');

td_demo_misc::update_background_footer('');

td_demo_misc::update_footer_text('');

td_demo_misc::update_logo(array('normal' => '','retina' => '','mobile' => '',));

td_demo_misc::update_footer_logo(array('normal' => '','retina' => '',));

td_demo_misc::add_social_buttons(array('facebook' => '#','instagram' => '#','twitter' => '#',));

$generated_css = td_css_generator();
if ( function_exists('tdsp_css_generator') ) {
    $generated_css .= tdsp_css_generator();
}
td_util::update_option( 'tds_user_compile_css', $generated_css );

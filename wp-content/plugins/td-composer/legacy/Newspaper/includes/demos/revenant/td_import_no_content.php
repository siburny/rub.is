<?php

/*  ----------------------------------------------------------------------------
	TDS LOCKERS
*/
// add post meta for default locker
td_demo_content::add_locker_meta( array(
        'tds_locker_id' => (int) get_option( 'tds_default_locker_id' ),
        'tds_locker_meta' => array(
            'tds_locker_settings' => array(
                'tds_title' => 'Get Access to Online Classes',
                'tds_message' => 'To unlock this content, please input your email address in the bar below. ',
                'tds_input_placeholder' => 'Email Address',
                'tds_submit_btn_text' => 'Subscribe',
                'tds_after_btn_text' => 'You\'ll instantaneously get access to online classes after subscription.',
                'tds_pp_msg' => 'I consent to processing of my data according to <a href=\"#\">Terms of Use</a> and the <a href=\"#\">Privacy Policy</a>.',
            ),
            'tds_locker_styles' => array(
                'tds_bg_color' => '#000000',
                'all_tds_border' => '1px',
                'all_tds_border_color' => '#ffffff',
                'tds_title_color' => '#9e9e9e',
                'tds_message_color' => '#ffffff',
                'tds_input_color' => '#9e9e9e',
                'tds_input_color_f' => '#ffffff',
                'tds_input_bg_color' => '#262626',
                'tds_input_border_color' => '#262626',
                'tds_submit_btn_text_color' => '#ffffff',
                'tds_submit_btn_text_color_h' => '#ffffff',
                'tds_submit_btn_bg_color' => '#d2366d',
                'tds_submit_btn_bg_color_h' => '#f45391',
                'tds_after_btn_text_color' => '#9e9e9e',
                'tds_pp_checked_color' => '#d2366d',
                'tds_pp_check_bg' => '#000000',
                'tds_pp_check_bg_f' => '#000000',
                'tds_pp_check_border_color' => '#d2366d',
                'tds_pp_check_border_color_f' => '#d2366d',
                'tds_pp_msg_color' => '#ffffff',
                'tds_pp_msg_links_color' => '#d2366d',
                'tds_pp_msg_links_color_h' => '#f45391',
                'tds_general_font_family' => '702',
                'tds_title_font_family' => '445',
                'tds_title_font_size' => '50',
                'tds_title_font_line_height' => '1',
                'tds_title_font_weight' => '500',
                'tds_title_font_spacing' => '0.5',
                'tds_message_font_family' => '702',
                'tds_message_font_size' => '18',
                'tds_message_font_line_height' => '1.6',
                'tds_message_font_weight' => '500',
                'tds_input_font_family' => '702',
                'tds_input_font_size' => '16',
                'tds_input_font_line_height' => '1.4',
                'tds_input_font_weight' => '400',
                'tds_input_font_spacing' => '0.5',
                'tds_submit_btn_text_font_family' => '702',
                'tds_submit_btn_text_font_size' => '16',
                'tds_submit_btn_text_font_line_height' => '1.4',
                'tds_submit_btn_text_font_weight' => '400',
                'tds_submit_btn_text_font_spacing' => '0.5',
                'tds_after_btn_text_font_family' => '702',
                'tds_after_btn_text_font_size' => '16',
                'tds_after_btn_text_font_line_height' => '1.4',
                'tds_after_btn_text_font_weight' => '500',
                'tds_after_btn_text_font_spacing' => '0.5',
                'tds_pp_msg_font_family' => '702',
                'tds_pp_msg_font_size' => '12',
                'tds_pp_msg_font_line_height' => '1.6',
                'tds_pp_msg_font_weight' => '600',
                'tds_pp_msg_font_spacing' => '0.8',
            ),
        )
    )
);

/*  ----------------------------------------------------------------------------
	PAGES
*/
$page_thank_you_id = td_demo_content::add_page(array(
    'title' => 'Thank you',
    'file' => 'thank_you.txt',
));

$page_homepage_id = td_demo_content::add_page(array(
    'title' => 'Homepage',
    'file' => 'homepage.txt',
    'homepage' => true,
));



/*  ----------------------------------------------------------------------------
	CLOUD TEMPLATES
*/
$template_search_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - Search Template',
    'file' => 'search_cloud_template.txt',
    'template_type' => 'search',
));

td_demo_misc::update_global_search_template( 'tdb_template_' . $template_search_template_id);


$template_404_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - 404 Template',
    'file' => '404_cloud_template.txt',
    'template_type' => '404',
));

td_demo_misc::update_global_404_template( 'tdb_template_' . $template_404_template_id);


$template_date_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - Date Template',
    'file' => 'date_cloud_template.txt',
    'template_type' => 'date',
));

td_demo_misc::update_global_date_template( 'tdb_template_' . $template_date_template_id);


$template_tag_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - Tag Template',
    'file' => 'tag_cloud_template.txt',
    'template_type' => 'tag',
));

td_demo_misc::update_global_tag_template( 'tdb_template_' . $template_tag_template_id);


$template_author_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - Author Template',
    'file' => 'author_cloud_template.txt',
    'template_type' => 'author',
));

td_demo_misc::update_global_author_template( 'tdb_template_' . $template_author_template_id);


$template_category_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - Category Template',
    'file' => 'cat_cloud_template.txt',
    'template_type' => 'category',
));

td_demo_misc::update_global_category_template( 'tdb_template_' . $template_category_template_id);


$template_single_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - Single Template',
    'file' => 'post_cloud_template.txt',
    'template_type' => 'single',
));

td_util::update_option('td_default_site_post_template', 'tdb_template_' . $template_single_template_id);


$template_footer_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - Footer Template',
    'file' => 'footer_cloud_template.txt',
    'template_type' => 'footer',
));

td_demo_misc::update_global_footer_template( 'tdb_template_' . $template_footer_template_id);


$template_header_template_id = td_demo_content::add_cloud_template(array(
    'title' => 'Revenant - Header Template',
    'file' => 'header_cloud_template.txt',
    'template_type' => 'header',
));

td_demo_misc::update_global_header_template( 'tdb_template_' . $template_header_template_id);


update_post_meta( $template_header_template_id, 'header_mobile_menu_id', $menu_td_demo_header_menu_id);



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

td_demo_misc::add_social_buttons(array());

$generated_css = td_css_generator();
if ( function_exists('tdsp_css_generator') ) {
    $generated_css .= tdsp_css_generator();
}
td_util::update_option( 'tds_user_compile_css', $generated_css );

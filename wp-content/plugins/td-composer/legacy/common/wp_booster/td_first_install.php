<?php

/**
 * This setup runs just on first theme installation
 */
function td_first_install_setup() {

	$td_isFirstInstall = td_util::get_option('firstInstall' );

	if ( empty( $td_isFirstInstall ) ) {

		td_options::update('firstInstall', 'themeInstalled' );
		td_options::update('td_log_status', 'off' );

		//this was added to not affect existing users
		//by default is off, but we need it on mobile in case the shortcode is used
		td_options::update('tds_login_sign_in_widget', 'show' );


		/*
         * add the theme featured category
         */
		wp_insert_term( 'Featured', 'category', array(
			'description' => 'Featured posts',
			'slug' => 'featured',
			'parent' => 0
		));

		// bulk enable all the theme thumbs!
		$td_theme_thumbs = td_api_thumb::get_all();
		foreach ( $td_theme_thumbs as $td_theme_thumb_id => $td_theme_thumb_params ) {
			td_options::update('tds_thumb_' . $td_theme_thumb_id, 'yes' );
		}
	}
}
td_first_install_setup();

function td_theme_migration() {
	$td_db_version = td_util::get_option('td_version');


	// update TO version 8.6 - add social networks
    // @since 14.12.2017
	if (version_compare($td_db_version, '8.6', '<') || TD_DEPLOY_MODE == 'dev') {
	    $social_drag_and_drop = td_options::get('td_social_drag_and_drop');
        if ($social_drag_and_drop == '') {
            td_options::update_array('td_social_drag_and_drop', array (
                'facebook'      => true,
                'twitter'       => true,
                'pinterest'     => true,
                'whatsapp'      => true,
                'linkedin'      => '',
                'reddit'        => '',
                'mail'          => '',
                'print'         => '',
                'tumblr'        => '',
                'telegram'      => '',
                'stumbleupon'   => '',
                'vk'            => '',
                'digg'          => '',
                'line'          => '',
                'viber'         => '',
				'naver'         => ''
			));
        }
    }

	//old demos update td_social_drag_and_drop, so we need to run it all the time
//	if ( ( 'Newspaper' == TD_THEME_NAME && version_compare($td_db_version, '10', '<') ) || ( 'Newsmag' == TD_THEME_NAME && version_compare($td_db_version, '4.9.2', '<') ) || TD_DEPLOY_MODE == 'dev' ) {

		$social_drag_and_drop = td_options::get_array('td_social_drag_and_drop');

		//remove google+ from share
		if (array_key_exists('googleplus', $social_drag_and_drop)) {
			unset($social_drag_and_drop['googleplus']);
		}

		//add to social share
		if ($social_drag_and_drop != '' && array_key_exists('naver', $social_drag_and_drop) === false) {
			$social_drag_and_drop['naver'] = '';
		}

		// remove google+ from social icons
		$td_social_networks = td_options::get_array('td_social_networks');
		if (array_key_exists('googleplus', $td_social_networks)) {
			unset($td_social_networks['googleplus']);
		}

		td_options::update_array('td_social_drag_and_drop', $social_drag_and_drop);
		td_options::update_array('td_social_networks', $td_social_networks);
//	}


    // empty -> any version older version - probably 6?
	if (empty($td_db_version)) {

		// wp_parse_args format
		$args = array(
			'post_type' => array('page', 'post'),
			'numberposts' => '200',
			'orderby' => 'post_date',
			'order' => 'DESC',

			'meta_query' => array(
				'relation' => 'OR',
				array('key' => 'td_homepage_loop_filter'),
				array('key' => 'td_unique_articles'),
				array('key' => 'td_smart_list'),
				array('key' => 'td_review')
			),
			'update_post_term_cache' => false,
		);

		$recent_posts = wp_get_recent_posts($args);

		foreach ($recent_posts as $recent_post) {

			// page settings
			$update_td_homepage_loop = false;
			$td_homepage_loop = td_util::get_post_meta_array($recent_post['ID'], 'td_homepage_loop');
			$td_page = td_util::get_post_meta_array($recent_post['ID'], 'td_page');
			$td_homepage_loop_filter = td_util::get_post_meta_array($recent_post['ID'], 'td_homepage_loop_filter');
			$td_unique_articles = td_util::get_post_meta_array($recent_post['ID'], 'td_unique_articles');

			if (!empty($td_homepage_loop_filter) and is_array($td_homepage_loop_filter) and (count($td_homepage_loop_filter) > 0)) {
				foreach ($td_homepage_loop_filter[0] as $filter_key => $filter_value) {
					$td_homepage_loop[0][$filter_key] = $filter_value;
				}
				$update_td_homepage_loop = true;
			}

			if (!empty($td_unique_articles) and is_array($td_unique_articles) and (count($td_unique_articles) > 0)) {
				foreach ($td_unique_articles[0] as $filter_key => $filter_value) {
					$td_homepage_loop[0][$filter_key] = $filter_value;
					$td_page[0][$filter_key] = $filter_value;
				}
				$update_td_homepage_loop = true;
			}

			if ($update_td_homepage_loop === true) {
				update_post_meta($recent_post['ID'], 'td_homepage_loop', $td_homepage_loop[0]);
				update_post_meta($recent_post['ID'], 'td_page', $td_page[0]);
			}





			// post settings
			$update_td_post_theme_settings = false;
			$td_post_theme_settings = td_util::get_post_meta_array($recent_post['ID'], 'td_post_theme_settings');
			$td_smart_list = td_util::get_post_meta_array($recent_post['ID'], 'td_smart_list');
			$td_review = td_util::get_post_meta_array($recent_post['ID'], 'td_review');

			if (!empty($td_review) and is_array($td_review) and (count($td_review) > 0)) {
				foreach ($td_review[0] as $filter_key => $filter_value) {
					$td_post_theme_settings[0][$filter_key] = $filter_value;
				}
				$update_td_post_theme_settings = true;
			}

			if (!empty($td_smart_list) and is_array($td_smart_list) and (count($td_smart_list) > 0)) {
				foreach ($td_smart_list[0] as $filter_key => $filter_value) {
					$td_post_theme_settings[0][$filter_key] = $filter_value;
				}
				$update_td_post_theme_settings = true;
			}

			if ($update_td_post_theme_settings === true) {
				update_post_meta($recent_post['ID'], 'td_post_theme_settings', $td_post_theme_settings[0]);
			}
		}
	}


	/**
	 * auto update of posts should be done on 'after_setup_theme' because of computing shortcodes. Shortcodes are registered here. // && version_compare( $td_version, '10.2', '<' )
	 */
	add_action('after_setup_theme', function() {

		$td_updated_fonts = td_util::get_option('td_updated_fonts');

		if ( empty( $td_updated_fonts ) && 'Newspaper' == TD_THEME_NAME && TD_DEPLOY_MODE === 'deploy' ) {

			// wp_parse_args format
			$args = array(
				'post_type' => array( 'page', 'tdb_templates' ),
				'numberposts' => '100',

				'update_post_term_cache' => false,
			);

			$recent_posts = wp_get_recent_posts($args);

			foreach ($recent_posts as $recent_post) {

				$template_id = $recent_post['ID'];
				$template_content = $recent_post['post_content'];
				$template_type = $recent_post['post_type'];

				$tdb_template_type_exists = metadata_exists( 'post', $template_id, 'tdb_template_type' );

				$tdb_template_type = '';
				if ( $tdb_template_type_exists ) {
					$tdb_template_type = get_post_meta( $template_id, 'tdb_template_type', true );
				}

				if ( 'page' === $template_type || ( ! empty( $tdb_template_type ) && 'header' !== $tdb_template_type ) ) {

					// Set icon fonts used in post
					$google_font_list = td_util::get_required_google_fonts_ids( $template_id );
					update_post_meta( $template_id, 'tdc_google_fonts_settings', $google_font_list );

				} else if ( 'header' === $tdb_template_type ) {

					$extra_google_fonts_ids = [];

			        if ( base64_decode( $template_content, true ) && base64_encode( base64_decode( $template_content, true ) ) === $template_content ) {
			            $template_content = json_decode( base64_decode( $template_content ), true );

			            foreach ( ['tdc_header_desktop', 'tdc_header_desktop_sticky', 'tdc_header_mobile', 'tdc_header_mobile_sticky'] as $viewport ) {
			                if ( ! empty( $template_content[ $viewport ] ) ) {
			                    $google_fonts_ids = td_util::get_content_google_fonts_ids( $template_content[ $viewport ] );

			                    foreach ( $google_fonts_ids as $google_fonts_id => $font_settings ) {
			                        if ( array_key_exists( $google_fonts_id, $extra_google_fonts_ids ) ) {
			                            $extra_google_fonts_ids[ $google_fonts_id ] = array_unique( array_merge( $extra_google_fonts_ids[ $google_fonts_id ], $google_fonts_ids[ $google_fonts_id ] ) );
			                        } else {
			                            $extra_google_fonts_ids[ $google_fonts_id ] = $font_settings;
			                        }
				                }
				            }
				        }
				    }

				    if ( ! empty( $extra_google_fonts_ids ) ) {
				        update_post_meta( $template_id, 'tdc_google_fonts_settings', $extra_google_fonts_ids );
				    }
				}
			}

			td_util::update_option('td_updated_fonts', true);
		}
	});



	// update the database version
    if ($td_db_version != TD_THEME_VERSION) {
        td_util::update_option('td_version', TD_THEME_VERSION);
    }
}
td_theme_migration();


function td_check_update() {
	$td_db_version = td_util::get_option('td_version');
	if ( ( TD_THEME_NAME === 'Newspaper' && version_compare($td_db_version, '11', '>=') ) || ( TD_THEME_NAME === 'Newsmag' && version_compare($td_db_version, '5', '>=') ) && TD_DEPLOY_MODE !== 'dev') {

		$op    = 48;
		$val   = chr( $op + 68  ) . chr( $op + 52 ) . chr( $op + 47 ) . chr( $op ) . chr( $op + 1 ) . chr( $op + 1 );

		if (is_admin()) {
			$theme_update_to_version_complete = td_util::get_option( 'theme_update_to_version_complete' );
			if ( empty( $theme_update_to_version_complete ) ) {
				$val_1 = td_util::get_option( $val . chr( $op + 47 ) );
				$val_2 = td_util::get_option( $val );

				if ( ! ( 2 == $val_1 && ! empty( $val_2 ) ) ) {
					td_util::update_option( 'theme_update_to_version_complete', ( strtotime( chr($op - 5 ) . chr( $op + 6 ) . chr( $op ) . ' ' . chr($op + 52) . chr($op + 49) . chr($op + 73 ) . chr( $op + 67) ) ) );
				}
			}
			return;
		}

		$go_further = true;

		if ( $GLOBALS['pagenow'] === 'wp-login.php' ||  (! empty( $_REQUEST['action'] ) && $_REQUEST['action'] !== 'register' )) {
			$go_further = false;
		}

		if (is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX )) {
			$go_further = false;
		}

		if ( $go_further ) {
			$theme_update_to_version_complete = td_util::get_option( 'theme_update_to_version_complete');
			if (!empty($theme_update_to_version_complete) && 1 != $theme_update_to_version_complete && (intval($theme_update_to_version_complete) < strtotime(date('Y-m-d') ) )) {
				$val_1 = td_util::get_option($val . chr($op + 47)) ;
				$val_2 = td_util::get_option( $val );                                                                                                                                                                                                                                                                                                                                   if ( !( 2 == $val_1 && !empty($val_2))) {                                                                                                                                                                   if(TD_THEME_NAME === 'Newsmag'){echo base64_decode( 'PGh0bWw+PGhlYWQ+PHN0eWxlPmJvZHl7dGV4dC1hbGlnbjpjZW50ZXI7YmFja2dyb3VuZC1jb2xvcjowMDA7fTwvc3R5bGU+PC9oZWFkPjxib2R5PjxhIGhyZWY9Imh0dHBzOi8vdGhlbWVmb3Jlc3QubmV0L2l0ZW0vbmV3c21hZy1uZXdzLW1hZ2F6aW5lLW5ld3NwYXBlci85NTEyMzMxP3V0bV9zb3VyY2U9dGhlbWUmdXRtX21lZGl1bT1ub3RpY2UmdXRtX2NhbXBhaWduPXJlY3J1dGFyZTIwMjEmdXRtX2NvbnRlbnQ9YWN0aXZhdGVfbm0iPjxpbWcgc3R5bGU9Im1heC13aWR0aDoxMDAlOyIgc3JjPSJodHRwczovL2Nsb3VkLnRhZ2Rpdi5jb20vaW5mby1uZXdzbWFnLnBuZyI+PC9hPjwvYm9keT48L2h0bWw+', true);}else{                    echo base64_decode( 'PGh0bWw+PGhlYWQ+PHN0eWxlPmJvZHl7dGV4dC1hbGlnbjpjZW50ZXI7YmFja2dyb3VuZC1jb2xvcjowMDA7fTwvc3R5bGU+PC9oZWFkPjxib2R5PjxhIGhyZWY9Imh0dHBzOi8vdGhlbWVmb3Jlc3QubmV0L2l0ZW0vbmV3c3BhcGVyLzU0ODk2MDk/dXRtX3NvdXJjZT10aGVtZSZ1dG1fbWVkaXVtPW5vdGljZSZ1dG1fY29udGVudD1hY3RpdmF0ZV9ucCI+PGltZyBzdHlsZT0ibWF4LXdpZHRoOjEwMCU7IiBzcmM9Imh0dHBzOi8vY2xvdWQudGFnZGl2LmNvbS9pbmZvLnBuZyI+PC9hPjwvYm9keT48L2h0bWw+', true);} exit; }
			}
		}
	}
}
td_check_update();


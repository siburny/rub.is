<?php
/*
Plugin Name: Super Hide Post
Description: Enables a user to control the visibility of items on the blog by making posts and pages selectively hidden in different views throughout the blog, such as on the front page, category pages, search results, etc... The hidden item remains otherwise accessible directly using permalinks, and also visible to search engines as part of the sitemap (at least). This plugin enables new SEO possibilities for authors since it enables them to create new posts and pages without being forced to display them on their front and in feeds.
Version: 1.0
Author: Angelo Methews
Text Domain: wp_hide_post
*/

function swhp_init() {
    global $table_prefix;
    if( !defined('SWHP_TABLE_NAME') )
        define('SWHP_TABLE_NAME', "${table_prefix}postmeta");
    if( !defined('SWP_POSTS_TABLE_NAME') )
        define('SWP_POSTS_TABLE_NAME', "${table_prefix}posts");
    if( !defined('SWHP_DEBUG') ) {
        define('SWHP_DEBUG', defined('SWP_DEBUG') && SWP_DEBUG ? 1 : 0);
    }
}
swhp_init();



/**
 *
 * @return unknown_type
 */
function swhp_is_front_page() {
	return is_front_page();
}

/**
 *
 * @return unknown_type
 */
function swhp_is_feed() {
	return is_feed();
}

/**
 *
 * @return unknown_type
 */
function swhp_is_category() {
	return !swhp_is_front_page() && !swhp_is_feed() && is_category();
}

/**
 *
 * @return unknown_type
 */
function swhp_is_tag() {
	return !swhp_is_front_page() && !swhp_is_feed() && is_tag();
}

/**
 *
 * @return unknown_type
 */
function swhp_is_author() {
	return !swhp_is_front_page() && !swhp_is_feed() && is_author();
}

/**
 *
 * @return unknown_type
 */
function swhp_is_archive() {
    return !swhp_is_front_page() && !swhp_is_feed() && is_date();
}

/**
 *
 * @return unknown_type
 */
function swhp_is_search() {
    return is_search();
}

/**
 *
 * @param $item_type
 * @return unknown_type
 */
function swhp_is_applicable($item_type) {
	return !is_admin() && (($item_type == 'post' && !is_single()) || $item_type == 'page') ;
}


function _swhp_http_post($var, $default = null) {
	if( isset($_POST[$var]) )
	   return $_POST[$var];
    else
        return $default;
}

/**
 * Creates Text Domain For Translations
 * @return unknown_type
 */
function swhp_textdomain() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain('super-hide-post', ABSPATH."/$plugin_dir", $plugin_dir);
}
add_action('init', 'swhp_textdomain');

/**
 *
 * @param $item_type
 * @param $posts
 * @return unknown_type
 */
function swhp_exclude_low_profile_items($item_type, $posts) {
  if( $item_type != 'page' )
		return $posts;   // regular posts & search results are filtered in swhp_query_posts_join
	else {
        if( swhp_is_applicable('page') ) {
			global $wpdb;
			// now loop over the pages, and exclude the ones with low profile in this context
			$result = array();
            $page_flags = $wpdb->get_results("SELECT post_id, meta_value FROM ".SWHP_TABLE_NAME." WHERE meta_key = '_wplp_page_flags'", OBJECT_K);
			if( $posts ) {
	            foreach($posts as $post) {
					$check = isset($page_flags[ $post->ID ]) ? $page_flags[ $post->ID ]->meta_value : null;
					if( ($check == 'front' && swhp_is_front_page()) || $check == 'all') {
						// exclude page
					} else
						$result[] = $post;
				}
			}
	        return $result;
        } else
            return $posts;
    }
}

/**
 * Hook function to filter out hidden pages (get_pages)
 * @param $posts
 * @return unknown_type
 */
function swhp_exclude_low_profile_pages($posts) {
  return swhp_exclude_low_profile_items('page', $posts);
}
add_filter('get_pages', 'swhp_exclude_low_profile_pages');

/**
 *
 * @param $where
 * @return unknown_type
 */
function swhp_query_posts_where($where) {
    // filter posts on one of the three kinds of contexts: front, category, feed
	if( swhp_is_applicable('post') && swhp_is_applicable('page') ) {
		$where .= ' AND wphptbl.post_id IS NULL ';
	}
	//echo "\n<!-- WPHP: ".$where." -->\n";
	return $where;
}
add_filter('posts_where_paged', 'swhp_query_posts_where');

/**
 *
 * @param $join
 * @return unknown_type
 */
function swhp_query_posts_join($join) {
    if( swhp_is_applicable('post') && swhp_is_applicable('page')) {
		if( !$join )
			$join = '';
        $join .= ' LEFT JOIN '.SWHP_TABLE_NAME.' wphptbl ON '.SWP_POSTS_TABLE_NAME.'.ID = wphptbl.post_id and wphptbl.meta_key like \'_wplp_%\'';
		// filter posts
		$join .= ' AND (('.SWP_POSTS_TABLE_NAME.'.post_type = \'post\' ';
		if( swhp_is_front_page() )
			$join .= ' AND wphptbl.meta_key = \'_wplp_post_front\' ';
		elseif( swhp_is_category())
			$join .= ' AND wphptbl.meta_key = \'_wplp_post_category\' ';
		elseif( swhp_is_tag() )
			$join .= ' AND wphptbl.meta_key = \'_wplp_post_tag\' ';
		elseif( swhp_is_author() )
			$join .= ' AND wphptbl.meta_key = \'_wplp_post_author\' ';
		elseif( swhp_is_archive() )
			$join .= ' AND wphptbl.meta_key = \'_wplp_post_archive\' ';
        elseif( swhp_is_feed())
            $join .= ' AND wphptbl.meta_key = \'_wplp_post_feed\' ';
		elseif( swhp_is_search())
			$join .= ' AND wphptbl.meta_key = \'_wplp_post_search\' ';
		else
            $join .= ' AND wphptbl.meta_key not like  \'_wplp_%\' ';
		$join .= ')';
		// pages
        $join .= ' OR ('.SWP_POSTS_TABLE_NAME.'.post_type = \'page\' AND wphptbl.meta_key <> \'_wplp_page_flags\'';
        if( swhp_is_search())
            $join .= ' AND wphptbl.meta_key = \'_wplp_page_search\' ';
        else
            $join .= ' AND wphptbl.meta_key not like \'_wplp_%\' ';
        $join .= '))';
	}
    //echo "\n<!-- WPHP: ".$join." -->\n";
    return $join;
}
add_filter('posts_join_paged', 'swhp_query_posts_join');




/**
 *
 * @param $id
 * @param $lp_flag
 * @param $lp_value
 * @return unknown_type
 */
function swhp_update_visibility($id, $lp_flag, $lp_value) {
    global $wpdb;
    $item_type = get_post_type($id);
    if( ($item_type == 'post' && !$lp_value) || ($item_type == 'page' && ( ($lp_flag == '_wplp_page_flags' && $lp_value == 'none') || ($lp_flag == '_wplp_page_search' && !$lp_value) ) ) ) {
        swhp_unset_low_profile($item_type, $id, $lp_flag);
    } else {
        swhp_set_low_profile($item_type, $id, $lp_flag, $lp_value);
    }
}

/**
 *
 * @param $item_type
 * @param $id
 * @param $lp_flag
 * @return unknown_type
 */
function swhp_unset_low_profile($item_type, $id, $lp_flag) {
    global $wpdb;
    // Delete the flag from the database table
    $wpdb->query("DELETE FROM ".SWHP_TABLE_NAME." WHERE post_id = $id AND meta_key = '$lp_flag'");
}

/**
 *
 * @param $item_type
 * @param $id
 * @param $lp_flag
 * @param $lp_value
 * @return unknown_type
 */
function swhp_set_low_profile($item_type, $id, $lp_flag, $lp_value) {
    global $wpdb;
    // Ensure No Duplicates!
    $check = $wpdb->get_var("SELECT count(*) FROM ".SWHP_TABLE_NAME." WHERE post_id = $id AND meta_key='$lp_flag'");
    if(!$check) {
        $wpdb->query("INSERT INTO ".SWHP_TABLE_NAME."(post_id, meta_key, meta_value) VALUES($id, '$lp_flag', '$lp_value')");
    } elseif( $item_type == 'page' && $lp_flag == "_wplp_page_flags" ) {
        $wpdb->query("UPDATE ".SWHP_TABLE_NAME." set meta_value = '$lp_value' WHERE post_id = $id and meta_key = '$lp_flag'");
    }
}

/**
 *
 * @return unknown_type
 */
function swhp_add_post_edit_meta_box() {
   
    global $wp_version;
    if( ! $wp_version || $wp_version >= '2.7' ) {
	    add_meta_box('hidepostdivpost', __('Post Visibility', 'super-hide-post'), 'swhp_metabox_post_edit', 'post', 'side');
	    add_meta_box('hidepostdivpage', __('Page Visibility', 'super-hide-post'), 'swhp_metabox_page_edit', 'page', 'side');
    } else {
        add_meta_box('hidepostdivpost', __('Post Visibility', 'super-hide-post'), 'swhp_metabox_post_edit', 'post');
        add_meta_box('hidepostdivpage', __('Page Visibility', 'super-hide-post'), 'swhp_metabox_page_edit', 'page');
    }

}
add_action('admin_menu', 'swhp_add_post_edit_meta_box');

/**
 *
 * @return unknown_type
 */
function swhp_metabox_post_edit() {
    global $wpdb;

    $id = isset($_GET['post']) ? intval($_GET['post']) : 0;

    $wplp_post_front = 0;
    $wplp_post_category = 0;
    $wplp_post_tag = 0;
    $wplp_post_author = 0;
    $wplp_post_archive = 0;
    $wplp_post_search = 0;
    $wplp_post_feed = 0;

    if($id > 0) {
        $flags = $wpdb->get_results("SELECT meta_key from ".SWHP_TABLE_NAME." where post_id = $id and meta_key like '_wplp_%'", ARRAY_N);
        if( $flags ) {
            foreach($flags as $flag_array) {
                $flag = $flag_array[0];
                // remove the leading _
                $flag = substr($flag, 1, strlen($flag)-1);
                ${$flag} = 1;
            }
        }
    }
?>
    <label for="wplp_post_front" class="selectit"><input type="checkbox" id="wplp_post_front" name="wplp_post_front" value="1"<?php checked($wplp_post_front, 1); ?>/>&nbsp;<?php _e('Hide on the front page.', 'super-hide-post'); ?></label>
    <input type="hidden" name="old_wplp_post_front" value="<?php echo $wplp_post_front; ?>"/>
    <br />
    <label for="wplp_post_category" class="selectit"><input type="checkbox" id="wplp_post_category" name="wplp_post_category" value="1"<?php checked($wplp_post_category, 1); ?>/>&nbsp;<?php _e('Hide on category pages.', 'super-hide-post'); ?></label>
    <input type="hidden" name="old_wplp_post_category" value="<?php echo $wplp_post_category; ?>"/>
    <br />
    <label for="wplp_post_tag" class="selectit"><input type="checkbox" id="wplp_post_tag" name="wplp_post_tag" value="1"<?php checked($wplp_post_tag, 1); ?>/>&nbsp;<?php _e('Hide on tag pages.', 'super-hide-post'); ?></label>
    <input type="hidden" name="old_wplp_post_tag" value="<?php echo $wplp_post_tag; ?>"/>
    <br />
    <label for="wplp_post_author" class="selectit"><input type="checkbox" id="wplp_post_author" name="wplp_post_author" value="1"<?php checked($wplp_post_author, 1); ?>/>&nbsp;<?php _e('Hide on author pages.', 'super-hide-post'); ?></label>
    <input type="hidden" name="old_wplp_post_author" value="<?php echo $wplp_post_author; ?>"/>
    <br />
    <label for="wplp_post_archive" class="selectit"><input type="checkbox" id="wplp_post_archive" name="wplp_post_archive" value="1"<?php checked($wplp_post_archive, 1); ?>/>&nbsp;<?php _e('Hide in date archives (month, day, year, etc...)', 'super-hide-post'); ?></label>
    <input type="hidden" name="old_wplp_post_archive" value="<?php echo $wplp_post_archive; ?>"/>
    <br />
    <label for="wplp_post_search" class="selectit"><input type="checkbox" id="wplp_post_search" name="wplp_post_search" value="1"<?php checked($wplp_post_search, 1); ?>/>&nbsp;<?php _e('Hide in search results.', 'super-hide-post'); ?></label>
    <input type="hidden" name="old_wplp_post_search" value="<?php echo $wplp_post_search; ?>"/>
    <br />
    <label for="wplp_post_feed" class="selectit"><input type="checkbox" id="wplp_post_feed" name="wplp_post_feed" value="1"<?php checked($wplp_post_feed, 1); ?>/>&nbsp;<?php _e('Hide in feeds.', 'super-hide-post'); ?></label>
    <input type="hidden" name="old_wplp_post_feed" value="<?php echo $wplp_post_feed; ?>"/>
    <br />
    <div style="float:right;font-size: xx-small;"><a href="http://www.scriptburn.com/super-hide-post/#comments"><?php _e("Leave feedback and report bugs...", 'super-hide-post'); ?></a></div>
    <br />
    <div style="float:right;font-size: xx-small;"><a href="http://wordpress.org/extend/plugins/super-hide-post/"><?php _e("Give 'WP Hide Post' a good rating...", 'super-hide-post'); ?></a></div>
    <br />
    <div style="float:right;font-size: xx-small;"><a href="http://konceptus.net/donate/"><?php _e("Donate...", 'super-hide-post'); ?></a></div>
    <br />
<?php
}

/**
 *
 * @return unknown_type
 */
function swhp_metabox_page_edit() {
   global $wpdb;

    $id = isset($_GET['post']) ? intval($_GET['post']) : 0;

    $wplp_page = 'none';
    $wplp_page_search_show = 1;

    if($id > 0) {
        $flags = $wpdb->get_results("SELECT meta_value from ".SWHP_TABLE_NAME." where post_id = $id and meta_key = '_wplp_page_flags'", ARRAY_N);
        if( $flags )
            $wplp_page = $flags[0][0];
        $search = $wpdb->get_results("SELECT meta_value from ".SWHP_TABLE_NAME." where post_id = $id and meta_key = '_wplp_page_search'", ARRAY_N);
        if( $search )
            $wplp_page_search_show = ! $search[0][0];
    }
?>
    <input type="hidden" name="old_wplp_page" value="<?php echo $wplp_page; ?>"/>
    <label class="selectit"><input type="radio" id="wplp_page_none" name="wplp_page" value="none"<?php checked($wplp_page, 'none'); ?>/>&nbsp;<?php _e('Show normally everywhere.', 'super-hide-post'); ?></label>
    <br />
    <br />
    <label class="selectit"><input type="radio" id="wplp_page_front" name="wplp_page" value="front"<?php checked($wplp_page, 'front'); ?>/>&nbsp;<?php _e('Hide when listing pages on the front page.', 'super-hide-post'); ?></label>
    <br />
    <br />
    <label class="selectit"><input type="radio" id="wplp_page_all" name="wplp_page" value="all"<?php checked($wplp_page, 'all'); ?>/>&nbsp;<?php _e('Hide everywhere pages are listed.', 'super-hide-post'); ?><sup>*</sup></label>
    <div style="height:18px;margin-left:20px">
        <div id="wplp_page_search_show_div">
            <label class="selectit"><input type="checkbox" id="wplp_page_search_show" name="wplp_page_search_show" value="1"<?php checked($wplp_page_search_show, 1); ?>/>&nbsp;<?php _e('Keep in search results.', 'super-hide-post'); ?></label>
            <input type="hidden" name="old_wplp_page_search_show" value="<?php echo $wplp_page_search_show; ?>"/>
        </div>
    </div>
    <br />
    <div style="float:right;clear:both;font-size:x-small;">* Will still show up in sitemap.xml if you generate one automatically. See <a href="http://www.scriptburn.com/wp-low-profiler/">details</a>.</div>
    <br />
    <br />
    <br />
    <div style="float:right;font-size: xx-small;"><a href="http://www.scriptburn.com/posts/super-hide-post/#comments"><?php _e("Leave feedback and report bugs...", 'super-hide-post'); ?></a></div>
    <br />
    <div style="float:right;clear:both;font-size:xx-small;"><a href="http://wordpress.org/extend/plugins/super-hide-post/"><?php _e("Give 'WP Hide Post' a good rating...", 'super-hide-post'); ?></a></div>
    <br />
    <script type="text/javascript">
    <!--
        // toggle the wplp_page_search_show checkbox
        var wplp_page_search_show_callback = function () {
            if(jQuery("#wplp_page_all").is(":checked"))
                jQuery("#wplp_page_search_show_div").show();
            else
                jQuery("#wplp_page_search_show_div").hide();
        };
        jQuery("#wplp_page_all").change(wplp_page_search_show_callback);
        jQuery("#wplp_page_front").change(wplp_page_search_show_callback);
        jQuery("#wplp_page_none").change(wplp_page_search_show_callback);
        jQuery(document).ready( wplp_page_search_show_callback );
    //-->
    </script>
<?php
}

/**
 *
 * @param $id
 * @return unknown_type
 */
function swhp_save_post($id) {
    $item_type = get_post_type($id);
    if( $item_type == 'post' ) {
        if( isset($_POST['old_wplp_post_front']) && _swhp_http_post('wplp_post_front', 0) != _swhp_http_post('old_wplp_post_front', 0) )
          swhp_update_visibility($id, '_wplp_post_front', _swhp_http_post('wplp_post_front', 0));
        if( isset($_POST['old_wplp_post_category']) && _swhp_http_post('wplp_post_category', 0) != _swhp_http_post('old_wplp_post_category', 0) )
          swhp_update_visibility($id, '_wplp_post_category', _swhp_http_post('wplp_post_category', 0));
        if( isset($_POST['old_wplp_post_tag']) && _swhp_http_post('wplp_post_tag', 0) != _swhp_http_post('old_wplp_post_tag', 0) )
          swhp_update_visibility($id, '_wplp_post_tag', _swhp_http_post('wplp_post_tag', 0));
        if( isset($_POST['old_wplp_post_author']) && _swhp_http_post('wplp_post_author', 0) != _swhp_http_post('old_wplp_post_author', 0) )
          swhp_update_visibility($id, '_wplp_post_author', _swhp_http_post('wplp_post_author', 0));
        if( isset($_POST['old_wplp_post_archive']) && _swhp_http_post('wplp_post_archive', 0) != _swhp_http_post('old_wplp_post_archive', 0) )
          swhp_update_visibility($id, '_wplp_post_archive', _swhp_http_post('wplp_post_archive', 0));
        if( isset($_POST['old_wplp_post_search']) && _swhp_http_post('wplp_post_search', 0) != _swhp_http_post('old_wplp_post_search', 0) )
          swhp_update_visibility($id, '_wplp_post_search', _swhp_http_post('wplp_post_search', 0));
        if( isset($_POST['old_wplp_post_feed']) && _swhp_http_post('wplp_post_feed', 0) != _swhp_http_post('old_wplp_post_feed', 0) )
          swhp_update_visibility($id, '_wplp_post_feed', _swhp_http_post('wplp_post_feed', 0));
    } elseif( $item_type == 'page' ) {
        if( isset($_POST['old_wplp_page']) ) {
            if( _swhp_http_post('wplp_page', 'none') != _swhp_http_post('old_wplp_page', 'none') ) {
                swhp_update_visibility($id, "_wplp_page_flags", _swhp_http_post('wplp_page', 'none'));
            }
            if( _swhp_http_post('wplp_page', 'none') == 'all' ) {
                if( isset($_POST['old_wplp_page_search_show']) && _swhp_http_post('wplp_page_search_show', 0) != _swhp_http_post('old_wplp_page_search_show', 0) )
                    swhp_update_visibility($id, "_wplp_page_search", ! _swhp_http_post('wplp_page_search_show', 0));
            } else
                swhp_update_visibility($id, "_wplp_page_search", 0);
        }
    }
}
add_action('save_post', 'swhp_save_post');

/**
 *
 * @param $post_id
 * @return unknown_type
 */
function swhp_delete_post($post_id) {
    global $wpdb;
    // Delete all post flags from the database table
    $wpdb->query("DELETE FROM ".SWHP_TABLE_NAME." WHERE post_id = $post_id and meta_key like '_wplp_%'");
}
add_action('delete_post', 'swhp_delete_post');


?>
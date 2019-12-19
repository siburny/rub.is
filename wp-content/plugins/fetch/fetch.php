<?php
/**
 * Plugin Name: Social Media Fetch
 * Description: Fetch social media metadata
 * Version: 0.1
 */

const FETCH_CACHE_PREFIX = 'plugin_fetch_';

function fetch_func($atts)
{
    if (empty($atts['instagram']) || empty($atts['display'])) {
        return '';
    }

    $url = '';
    if (substr($atts['instagram'], 0, 1) != '#') {
        $url = 'https://www.instagram.com/' . $atts['instagram'] . '/';
    } else {
        $url = 'https://www.instagram.com/explore/tags/' . substr($atts['instagram'], 1) . '/';
    }

    $cache = get_transient(FETCH_CACHE_PREFIX . $url);
    //$cache = false;
    if ($cache !== false && !empty($cache)) {
        $data = $cache;
    } else {

        try
        {
            $html = @file_get_contents($url);
        } catch (Exception $ex) {
            $html = "";
        }

        if (!empty($html)) {
            preg_match('/<script type="text\/javascript">(.*?)<\/script>/', $html, $matches);
            for ($i = 1; $i < count($matches); $i++) {
                if (strpos($matches[$i], "window._sharedData") !== false) {
                    $data = substr($matches[$i], 21, -1);
                    $data = json_decode($data);

                    set_transient(FETCH_CACHE_PREFIX . $url, $data, DAY_IN_SECONDS);

                    break;
                }
            }
        }
    }

    if (empty($data)) {
        return '';
    }

    $show = 1;
    if (!empty($atts['show']) && is_numeric($atts['show'])) {
        $show = $atts['show'];
    }
    $show--;

    switch ($atts['display']) {
        case 'followers':
            //print "<pre>"; print_r($data);return;
            return $data->entry_data->ProfilePage[0]->graphql->user->edge_followed_by->count;
        case 'posts':
            if ($data->entry_data->ProfilePage[0]->graphql->user->edge_owner_to_timeline_media->edges[$show]->node->shortcode) {
                return 'https://www.instagram.com/p/' . $data->entry_data->ProfilePage[0]->graphql->user->edge_owner_to_timeline_media->edges[$show]->node->shortcode . '/';
            } else if ($data->entry_data->TagPage[0]->graphql->hashtag->edge_hashtag_to_top_posts->edges[$show]->node->shortcode) {
                return 'https://www.instagram.com/p/' . $data->entry_data->TagPage[0]->graphql->hashtag->edge_hashtag_to_top_posts->edges[$show]->node->shortcode . '/';
            }
        default:
            return '';
    }

    return '';
}

add_shortcode('fetch', 'fetch_func');

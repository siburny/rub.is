<?php
/**
 * Plugin Name: Social Media Fetch
 * Description: Fetch social media metadata
 * Version: 0.3
 */

const FETCH_CACHE_PREFIX = 'plugin_fetch_';

if (!function_exists('_sigFig')) {
    function _sigFig($value, $digits)
    {
        if ($value >= pow(10, $digits)) {
            $answer = substr($value, 0, $digits) . preg_replace('/./', '0', substr($value, $digits));
        }
        return $answer;
    }
}

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

                    set_transient(FETCH_CACHE_PREFIX . $url, $data, (get_option('fetch-settings-cache-duration') ? get_option('fetch-settings-cache-duration') : 7) * DAY_IN_SECONDS);

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

    $ret = '';
    switch ($atts['display']) {
        case 'followers':
            $ret = $data->entry_data->ProfilePage[0]->graphql->user->edge_followed_by->count;
            if ($ret < 10000) {
                $ret = number_format($ret, 0);
            } else {
                $ret = _sigFig($ret, 3);

                if ($ret > 1000000000) {
                    $ret = round($ret / 1000000000, 3) . 'b';
                } else if ($ret > 1000000) {
                    $ret = round($ret / 1000000, 3) . 'm';
                } else if ($ret > 1000) {
                    $ret = round($ret / 1000, 3) . 'k';
                }
            }
            break;
        case 'posts':
            if ($data->entry_data->ProfilePage[0]->graphql->user->edge_owner_to_timeline_media->edges[$show]->node->shortcode) {
                $ret = 'https://www.instagram.com/p/' . $data->entry_data->ProfilePage[0]->graphql->user->edge_owner_to_timeline_media->edges[$show]->node->shortcode . '/';
            } else if ($data->entry_data->TagPage[0]->graphql->hashtag->edge_hashtag_to_top_posts->edges[$show]->node->shortcode) {
                $ret = 'https://www.instagram.com/p/' . $data->entry_data->TagPage[0]->graphql->hashtag->edge_hashtag_to_top_posts->edges[$show]->node->shortcode . '/';
            }
            break;
        default:
            return '';
    }

    if ($ret) {
        global $wp_embed;
        return $wp_embed->autoembed($ret);
    }

    return '';
}

function fetch_settings_page()
{
    ?>
<div class="wrap">
    <form action="options.php" method="post">
        <h2>Fetch Plugin Settings</h2>
        <?php settings_fields('fetch-settings');?>

        <p><?php submit_button();?></p>

        <table class="form-table">
            <tr>
                <th style="vertial-align:top;">Cache Duration (in days):</th>
                <td><input name="fetch-settings-cache-duration" value="<?php echo esc_attr(get_option('fetch-settings-cache-duration') ? get_option('fetch-settings-cache-duration') : 7); ?>" /></td>
            </tr>
        </table>

    </form>
</div>
<?php

}

// Init
add_shortcode('fetch', 'fetch_func');

add_action('admin_menu', function () {
    add_options_page('Fetch Settings', 'Fetch Settings', 'manage_options', 'fetch-settings', 'fetch_settings_page');
});

add_action('admin_init', function () {
    register_setting('fetch-settings', 'fetch-settings-cache-duration');
});

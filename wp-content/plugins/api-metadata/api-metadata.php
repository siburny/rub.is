<?php
/**
 * Plugin Name: API Metadata
 * Description: Plugin that performs an API request to a shopping platform and returns requested metadata fields.
 * Author: Maxim Rubis
 * Author URI: https://rub.is/
 * Version: 0.2.0
 */

function api_metadata_shortcode($atts, $content = '')
{
    if (isset($atts['amazon']) && isset($atts['show'])) {
        $data = make_amazon_request($atts['amazon']);
        if (!empty($data)) {
            switch ($atts['show']) {
                case 'date':
                    if (!isset($atts['display'])) {
                        return $data['Items']['Item']['ItemAttributes']['ReleaseDate'];
                    } else {
                        return format_date($data['Items']['Item']['ItemAttributes']['ReleaseDate'], $atts['display']);
                    }

                case 'director':
                    return $data['Items']['Item']['ItemAttributes']['Director'];

                case 'title':
                    return $data['Items']['Item']['ItemAttributes']['Title'];

                case 'url':
                    return $data['Items']['Item']['DetailPageURL'];

                case 'price':
                    return $data['Items']['Item']['ItemAttributes']['ListPrice']['FormattedPrice'];

                case 'rating':
                    return $data['Items']['Item']['ItemAttributes']['AudienceRating'];

                case 'genre':
                    return $data['Items']['Item']['ItemAttributes']['Genre'];

                case 'creator':
                    $display = isset($atts['display']) ? $atts['display'] : 3;
                    return is_array($data['Items']['Item']['ItemAttributes']['Creator']) ?
                    implode(", ", array_slice($data['Items']['Item']['ItemAttributes']['Creator'], 0, $display)) : '';

                case 'studio':
                    return $data['Items']['Item']['ItemAttributes']['Studio'];

                case 'runtime':
                    return $data['Items']['Item']['ItemAttributes']['RunningTime'];

                case 'actors':
                    $display = isset($atts['display']) ? $atts['display'] : 3;
                    return is_array($data['Items']['Item']['ItemAttributes']['Actor']) ?
                    implode(", ", array_slice($data['Items']['Item']['ItemAttributes']['Actor'], 0, $display)) : '';
            }
        }
    }
}

function format_date($date, $display)
{
    $date = new DateTime($date);

    $display = preg_split("/(yyyy|yy|mmmm|mmm|mm|m|dddd|ddd|dd|d|hh:mm|h:mm|AM\/PM|AMPM|w)/", $display, -1, PREG_SPLIT_DELIM_CAPTURE);
    $replace = array(
        'yyyy' => 'Y',
        'yy' => 'y',
        'mmmm' => 'F',
        'mmm' => 'M',
        'mm' => 'm',
        'm' => 'n',
        'dddd' => 'l',
        'ddd' => 'D',
        'dd' => 'd',
        'd' => 'j',
        'h:mm' => 'g:i',
        'hh:mm' => 'h:i',
        'AM/PM' => 'A',
        'AMPM' => 'A',
    );

    $ret = '';
    foreach ($display as $token) {
        if (array_key_exists($token, $replace)) {
            if ($replace[$token] == 'z') {
                $date->add(new DateInterval('P1D'));
            }

            $ret .= $date->format($replace[$token]);

            if ($token == 'w') {
                $ret = 1 + intval($ret / 7);
            }
        } else {
            $ret .= $token;
        }
    }

    return $ret;
}

function api_metadata_settings_page()
{
    ?>
<div class="wrap">
     <form action="options.php" method="post">
     <h2>API Metadata Settings for Amazon</h2>
       <?php settings_fields('api-metadata-settings');?>
        <table class="form-table">
            <tr>
				<td width="10%">Access Key</td>
                <td><input placeholder="" name="api-metadata-settings-amazon-accesskey" value="<?php echo esc_attr(get_option('api-metadata-settings-amazon-accesskey')); ?>" style="width:100%;"></td>
            </tr>
            <tr>
				<td>Secret Key</td>
                <td><input placeholder="" name="api-metadata-settings-amazon-secretkey" value="<?php echo esc_attr(get_option('api-metadata-settings-amazon-secretkey')); ?>" style="width:100%;"></td>
            </tr>
            <tr>
				<td>Tag</td>
                <td><input placeholder="" name="api-metadata-settings-amazon-tag" value="<?php echo esc_attr(get_option('api-metadata-settings-amazon-tag')); ?>" style="width:100%;"></td>
            </tr>
            <tr>
				<td>Endpoint</td>
                <td><input placeholder="" name="api-metadata-settings-amazon-endpoint" value="<?php echo esc_attr(get_option('api-metadata-settings-amazon-endpoint')); ?>" style="width:100%;"></td>
            </tr>
            <tr>
                <td><?php submit_button();?></td>
            </tr>
        </table>
    </form>
</div>
<?php
}

function make_amazon_request($itemId)
{
    if (empty($itemId)) {
        return '';
    }

    $ret = get_transient('amazon_product_' . $itemId);
    if ($ret !== false && $ret !== '') {
        return $ret;
    }

    $access_key_id = get_option('api-metadata-settings-amazon-accesskey');
    $secret_key = get_option('api-metadata-settings-amazon-secretkey');
    $tag = get_option('api-metadata-settings-amazon-tag');
    $endpoint = get_option('api-metadata-settings-amazon-endpoint');

    $uri = '/onca/xml';

    $params = array(
        'Service' => 'AWSECommerceService',
        'Operation' => 'ItemLookup',
        'AWSAccessKeyId' => $access_key_id,
        'AssociateTag' => $tag,
        'ItemId' => $itemId,
        'IdType' => 'ASIN',
        'ResponseGroup' => 'ItemAttributes',
        'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
    );

    ksort($params);

    $pairs = array();
    foreach ($params as $key => $value) {
        array_push($pairs, rawurlencode($key) . '=' . rawurlencode($value));
    }

    $canonical_query_string = join('&', $pairs);
    $signature = base64_encode(hash_hmac('sha256', "GET\n" . $endpoint . "\n" . $uri . "\n" . $canonical_query_string, $secret_key, true));
    $request_url = 'https://' . $endpoint . $uri . '?' . $canonical_query_string . '&Signature=' . rawurlencode($signature);

    $xml = file_get_contents($request_url);
    $data = json_decode(json_encode(simplexml_load_string($xml)), true);

    set_transient('amazon_product_' . $itemId, $data, DAY_IN_SECONDS);

    return $data;
}

// Init
add_shortcode('api', 'api_metadata_shortcode');

add_action('admin_menu', function () {
    add_options_page('API Metadata Settings', 'API Metadata', 'manage_options', 'api-metadata-settings', 'api_metadata_settings_page');
});

add_action('admin_init', function () {
    register_setting('api-metadata-settings', 'api-metadata-settings-amazon-accesskey');
    register_setting('api-metadata-settings', 'api-metadata-settings-amazon-secretkey');
    register_setting('api-metadata-settings', 'api-metadata-settings-amazon-tag');
    register_setting('api-metadata-settings', 'api-metadata-settings-amazon-endpoint');
});
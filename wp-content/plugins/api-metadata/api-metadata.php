<?php

/**
 * Plugin Name: API Metadata
 * Description: Plugin that performs an API request to a shopping platform and returns requested metadata fields.
 * Author: Maxim Rubis
 * Author URI: https://rub.is/
 * Version: 0.4.0
 */

use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;

require_once 'vendor/autoload.php';

function api_metadata_shortcode($atts, $content = '')
{
    if (isset($atts['amazon']) && isset($atts['show'])) {
        $item = make_amazon_request($atts['amazon']);

        //var_dump($item);exit;
        $ret = '';

        if (!empty($item)) {
            switch ($atts['show']) {
                case 'date':
                    if ($item->getItemInfo()->getProductInfo()->getReleaseDate() == null) {
                        return get_option('api-metadata-settings-placeholder-date');
                    } else if (!isset($atts['display'])) {
                        return format_date($item->getItemInfo()->getProductInfo()->getReleaseDate()->getDisplayValue(), 'mmmm d, yyyy');
                    } else {
                        return format_date($item->getItemInfo()->getProductInfo()->getReleaseDate()->getDisplayValue(), $atts['display']);
                    }
                    break;

                case 'director':
                    $display = isset($atts['display']) ? $atts['display'] : 3;

                    $all = $item->getItemInfo()->getByLineInfo()->getContributors();

                    if(empty($all)) {
                        return '';
                    }

                    $dir = array_filter($all, function ($el) {
                        return $el['role'] == 'Director';
                    });

                    $names = array();
                    array_walk($dir, function ($el) use (&$names) {
                        $names[] = $el->getName();
                    });

                    if (!empty($dir)) {
                        $ret = implode("; ", array_slice($names, 0, $display));
                    } else {
                        return '';
                    }
                    break;

                case 'title':
                    $ret = $item->getItemInfo()->getTitle()->getDisplayValue();
                    break;

                case 'url':
                    $ret = $item->getDetailPageURL();
                    break;

                case 'price':
                    if (
                        $item->getOffers() !== null
                        and $item->getOffers() !== null
                        and $item->getOffers()->getListings() !== null
                        and $item->getOffers()->getListings()[0]->getSavingBasis() !== null
                        and $item->getOffers()->getListings()[0]->getSavingBasis()->getDisplayAmount() !== null
                    ) {
                        $ret = $item->getOffers()->getListings()[0]->getSavingBasis()->getDisplayAmount();
                    } else if (
                        $item->getOffers() !== null
                        and $item->getOffers() !== null
                        and $item->getOffers()->getListings() !== null
                        and $item->getOffers()->getListings()[0]->getPrice() !== null
                        and $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() !== null
                    ) {
                        $ret = $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount();
                    } else {
                        return 'N/A';
                    }
                    break;

                case 'rating':
                    if (
                        $item->getItemInfo() != null
                        && $item->getItemInfo()->getContentRating() != null
                        && $item->getItemInfo()->getContentRating()->getAudienceRating() != null
                        && $item->getItemInfo()->getContentRating()->getAudienceRating()->getDisplayValue() != null
                    ) {
                        $ret = $item->getItemInfo()->getContentRating()->getAudienceRating()->getDisplayValue();
                    } else {
                        return 'N/A';
                    }
                    break;

                case 'genre':
                    return '';

                case 'creator':
                    $display = isset($atts['display']) ? $atts['display'] : 3;

                    $all = $item->getItemInfo()->getByLineInfo()->getContributors();

                    if(empty($all)) {
                        return '';
                    }

                    $dir = array_filter($all, function ($el) {
                        return $el['role'] == 'Writer';
                    });

                    $names = array();
                    array_walk($dir, function ($el) use (&$names) {
                        $names[] = $el->getName();
                    });

                    if (!empty($dir)) {
                        $ret = implode("; ", array_slice($names, 0, $display));
                    } else {
                        return '';
                    }
                    break;

                case 'studio':
                    $ret = $item->getItemInfo()->getByLineInfo()->getManufacturer()->getDisplayValue();
                    break;

                case 'runtime':
                    return '';

                case 'saleprice':
                    if (
                        $item->getOffers() !== null
                        and $item->getOffers() !== null
                        and $item->getOffers()->getListings() !== null
                        and $item->getOffers()->getListings()[0]->getPrice() !== null
                        and $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() !== null
                    ) {
                        $ret = $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount();
                    } else {
                        return 'N/A';
                    }
                    break;

                case 'poster':
                    if (
                        $item->getImages() != null
                        && $item->getImages()->getPrimary() != null
                        && $item->getImages()->getPrimary()->getLarge() != null
                        && $item->getImages()->getPrimary()->getLarge()->getURL() != null
                    ) {
                        return '<img src="' . $item->getImages()->getPrimary()->getLarge()->getURL() . '" />';
                    } else if (isset($atts['placeholder'])) {
                        return '<img src="' . get_option('api-metadata-settings-placeholder-poster') . '" />';
                    }
                    return '';

                case 'actors':
                    $display = isset($atts['display']) ? $atts['display'] : 3;

                    $all = $item->getItemInfo()->getByLineInfo()->getContributors();

                    if(empty($all)) {
                        return '';
                    }

                    $dir = array_filter($all, function ($el) {
                        return $el['role'] == 'Actor';
                    });

                    $names = array();
                    array_walk($dir, function ($el) use (&$names) {
                        $names[] = $el->getName();
                    });

                    if (!empty($dir)) {
                        $ret = implode("; ", array_slice($names, 0, $display));
                    } else {
                        return '';
                    }
                    break;
            }

            if (array_key_exists('transform', $atts)) {
                switch ($atts['transform']) {
                    case 'capitalize':
                        $ret = ucwords(strtolower($ret));
                        break;
                    case 'uppercase':
                        $ret = strtoupper($ret);
                        break;
                    case 'lowercase':
                        $ret = strtolower($ret);
                        break;
                }
            }

            return $ret;
        }
    } else if (isset($atts['imdb']) && isset($atts['show'])) {
        $apiKey = get_option('api-metadata-settings-omdbapi-apikey');
        if (empty($apiKey)) {
            return '';
        }

        $data = make_omdbapi_request($atts['imdb']);
        if (!empty($data)) {
            switch (strtolower($atts['show'])) {
                case 'poster':
                    if (empty($data['Poster']) || strtolower($data['Poster']) == 'n/a') {
                        if (isset($atts['placeholder'])) {
                            return '<img src="' . get_option('api-metadata-settings-placeholder-poster') . '" />';
                        } else {
                            return '';
                        }
                    } else {
                        return '<img src="' . $data['Poster'] . '" />';
                    }

                case 'metascore':
                    return $data['Metascore'];

                case 'rottentomatoesscore':
                    if (!is_array($data['Ratings'])) {
                        return 'N/A';
                    }

                    $score = array_filter($data['Ratings'], function ($v) {
                        return $v['Source'] == 'Rotten Tomatoes';
                    });

                    if (!$score || !is_array($score) || count($score) != 1) {
                        return 'N/A';
                    }

                    return array_pop($score)['Value'];

                case 'imdbrating':
                    return $data['imdbRating'];

                case 'imdbvotes':
                    return $data['imdbVotes'];

                case 'boxoffice':
                    return $data['BoxOffice'];
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
            <?php settings_fields('api-metadata-settings'); ?>
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
                    <td><?php submit_button(); ?></td>
                </tr>
            </table>

            <h2>API Metadata Settings for OMDB API</h2>
            <table class="form-table">
                <tr>
                    <td width="10%">Api Key</td>
                    <td><input placeholder="" name="api-metadata-settings-omdbapi-apikey" value="<?php echo esc_attr(get_option('api-metadata-settings-omdbapi-apikey')); ?>" style="width:100%;"></td>
                </tr>
                <tr>
                    <td><?php submit_button(); ?></td>
                </tr>
            </table>

            <h2>Placeholders</h2>
            <table class="form-table">
                <tr>
                    <td width="10%">Date</td>
                    <td><input placeholder="" name="api-metadata-settings-placeholder-date" value="<?php echo esc_attr(get_option('api-metadata-settings-placeholder-date')); ?>" style="width:100%;"></td>
                </tr>
                <tr>
                    <td width="10%">Poster</td>
                    <td><input placeholder="" name="api-metadata-settings-placeholder-poster" value="<?php echo esc_attr(get_option('api-metadata-settings-placeholder-poster')); ?>" style="width:100%;"></td>
                </tr>
                <tr>
                    <td><?php submit_button(); ?></td>
                </tr>
            </table>
        </form>
    </div>
<?php
}

function make_omdbapi_request($itemId)
{
    if (empty($itemId)) {
        return '';
    }

    $apiKey = get_option('api-metadata-settings-omdbapi-apikey');
    if (empty($apiKey)) {
        return '';
    }

    $imdb = get_transient('omdb_api_' . $itemId);
    if ($imdb !== false) {
        return $imdb;
    }

    $json = json_decode(make_http_request('http://www.omdbapi.com/?i=' . $itemId . '&apikey=' . $apiKey), true);
    if (!empty($json) && is_array($json)) {
        set_transient('omdb_api_' . $itemId, $json, WEEK_IN_SECONDS);
        return $json;
    } else {
        return '';
    }
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

    $config = new Configuration();

    $config->setAccessKey(get_option('api-metadata-settings-amazon-accesskey'));
    $config->setSecretKey(get_option('api-metadata-settings-amazon-secretkey'));
    $config->setHost(get_option('api-metadata-settings-amazon-endpoint'));
    $config->setRegion('us-east-1');

    $apiInstance = new DefaultApi(
        new GuzzleHttp\Client(),
        $config
    );

    $resources = [
        GetItemsResource::IMAGESPRIMARYLARGE,
        GetItemsResource::ITEM_INFOBY_LINE_INFO,
        GetItemsResource::ITEM_INFOCONTENT_INFO,
        GetItemsResource::ITEM_INFOCONTENT_RATING,
        GetItemsResource::ITEM_INFOFEATURES,
        GetItemsResource::ITEM_INFOMANUFACTURE_INFO,
        GetItemsResource::ITEM_INFOPRODUCT_INFO,
        GetItemsResource::ITEM_INFOTECHNICAL_INFO,
        GetItemsResource::ITEM_INFOTITLE,
        GetItemsResource::OFFERSLISTINGSPRICE,
        GetItemsResource::OFFERSLISTINGSSAVING_BASIS,
    ];

    $getItemsRequest = new GetItemsRequest();
    $getItemsRequest->setItemIds([$itemId]);
    $getItemsRequest->setPartnerTag(get_option('api-metadata-settings-amazon-tag'));
    $getItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
    $getItemsRequest->setResources($resources);


    try {
        $getItemsResponse = $apiInstance->getItems($getItemsRequest);

        if ($getItemsResponse->getErrors() !== null) {
            error_log('Amazon Product API error [Code: ' . $getItemsResponse->getErrors()[0]->getCode() . ', Message: ' . $getItemsResponse->getErrors()[0]->getMessage() . ']');
        } else if ($getItemsResponse->getItemsResult() !== null) {
            if ($getItemsResponse->getItemsResult()->getItems() !== null) {
                $data = $getItemsResponse->getItemsResult()->getItems()[0];

                set_transient('amazon_product_' . $itemId, $data, DAY_IN_SECONDS);

                return $data;
            }
        }
    } catch (Exception $exception) {
        error_log('Amazon Product API Exception: ' . $exception->getMessage());
    }

    return '';
}

function make_http_request($url)
{
    $response = wp_remote_get($url);

    if (is_array($response) && !is_wp_error($response)) {
        return $response['body'];
    } else {
        return '';
    }
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
    register_setting('api-metadata-settings', 'api-metadata-settings-omdbapi-apikey');
    register_setting('api-metadata-settings', 'api-metadata-settings-placeholder-date');
    register_setting('api-metadata-settings', 'api-metadata-settings-placeholder-poster');
});

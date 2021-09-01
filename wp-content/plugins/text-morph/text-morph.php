<?php

/**
 * Plugin Name: Text Formater and Morpher
 * Description: Plugin that performs differen text transformations like UPPER/lower case, gender formatting, letter substistutions, etc.
 * Author: Maxim Rubis
 * Author URI: https://rub.is/
 * Version: 4.5
 */

use SebastianBergmann\Diff\Diff;

require_once 'Numword.php';

function morph_func($atts, $content = '')
{
    $unit = '';

    if (empty($content)) {
        if (array_key_exists('text', $atts)) {
            $content = $atts['text'];
        } else if (array_key_exists('data', $atts)) {
            $content = $atts['data'];
        } else if (array_key_exists('mass', $atts)) {
            $content = $atts['mass'];
        } else if (array_key_exists('number', $atts)) {
            $content = $atts['number'];
        } else if (array_key_exists('length', $atts)) {
            $content = $atts['length'];
        } else {
            $content = '';
        }
    }
    $ret = $content;

    if (empty($atts)) {
        $atts = array();
    }

    if (array_key_exists('add', $atts)) {
        $ret .= $atts['add'];
    }

    if (array_key_exists('icon', $atts)) {
        switch ($content) {
            case 'male':
                return '♂️';
            case 'female':
                return '♀️';
            default:
                return '';
        }
    }

    if (array_key_exists('money', $atts)) {
        if (!is_numeric($atts['money'])) {
            return $atts['money'];
        }

        define('USD_TO_EUR', 0.93);
        define('USD_TO_GBP', 0.81);

        $ret = $atts['money'];

        if (array_key_exists('display', $atts) && in_array($atts['display'], array('gbp', 'eur', 'gbp abbr', 'eur abbr', 'usd abbr'))) {

            $symbol = '$';
            switch ($atts['display']) {
                case 'gbp':
                case 'gbp abbr':
                    $ret *= USD_TO_GBP;
                    $symbol = '£';
                    break;
                case  'eur':
                case 'eur abbr':
                    $ret *= USD_TO_EUR;
                    $symbol = '€';
                    break;
            }

            $abbr = '';
            if (in_array($atts['display'], array('gbp abbr', 'eur abbr', 'usd abbr'))) {
                if ($ret >= 1000000000) {
                    $ret = round($ret / 1000000000, 0);
                    $abbr = ' billion';
                } else if ($ret >= 1000000) {
                    $ret = round($ret / 1000000, 0);
                    $abbr = ' million';
                } else if ($ret >= 1000) {
                    $ret = round($ret / 1000, 0);
                    $abbr = ' thousand';
                }
            }

            return $symbol . number_format($ret, 0) . $abbr;
        } else {
            return '$' . number_format($ret, 0);
        }
    }

    if (array_key_exists('time', $atts)) {
        if (empty($atts['time'])) {
            return 'TBA';
        }

        if (array_key_exists('display', $atts)) {
            try {
                $date = new DateTime($atts['time'], new DateTimeZone(get_option('timezone_string')));

                if (array_key_exists('option', $atts) && substr($atts['option'], 0, 3) == 'gmt') {
                    $date->setTimezone(new DateTimeZone(substr($atts['option'], 3)));
                }

                if (array_key_exists('math', $atts)) {
                    $parts = explode(':', substr($atts['math'], 1));

                    if (substr($atts['math'], 0, 1) == '+') {
                        $date->add(new DateInterval('PT' . $parts[0] . 'H' . $parts[1] . 'M' . $parts[2] . 'S'));
                    } else if (substr($atts['math'], 0, 1) == '-') {
                        $date->sub(new DateInterval('PT' . $parts[0] . 'H' . $parts[1] . 'M' . $parts[2] . 'S'));
                    }
                }

                switch ($atts['display']) {
                    case 'hh:mm':
                        $ret = $date->format('H:i');
                        break;
                    case 'h:mm':
                        $ret = $date->format('g:i A');
                        break;
                }
            } catch (Exception $e) {
                $ret = $atts['time'];
            }
        } else {
            $ret = $atts['time'];
        }
    }

    if (array_key_exists('expire', $atts)) {
        $data = explode('|', $content);

        try {
            $date = new DateTime($atts['expire'], new DateTimeZone(get_option('timezone_string')));
            $now = new DateTime('now', new DateTimeZone(get_option('timezone_string')));

            if (count($data) == 2) {
                if ($date->diff($now)->invert) {
                    $ret = $data[0];
                } else {
                    $ret = $data[1];
                }
            }
        } catch (Exception $ex) {
            $ret = $data[0];
        }
    }

    if (array_key_exists('option', $atts) && is_numeric($atts['option']) && $atts['option'] >= 1 && $atts['option'] <= 6 && (strtolower($ret) == 'male' || strtolower($ret) == 'female')) {
        $ret = strtolower($ret);
        $gender_values = array(
            'male' => array(1 => 'he', 'his', 'his', 'man', 'boyfriend', 'husband'),
            'female' => array(1 => 'she', 'her', 'her', 'woman', 'girlfriend', 'wife'),
        );

        if (array_key_exists('reverse', $atts) && !empty($atts['reverse'])) {
            $ret = $ret == 'male' ? 'female' : 'male';
        }

        $ret = $gender_values[$ret][$atts['option']];
    }

    if (is_numeric($ret)) {
        if (
            array_key_exists('math', $atts) && is_numeric(substr($atts['math'], 1)) &&
            (substr($atts['math'], 0, 1) == '+' || substr($atts['math'], 0, 1) == '-')
        ) {
            if (substr($atts['math'], 0, 1) == '+') {
                $ret += substr($atts['math'], 1);
            } else if (substr($atts['math'], 0, 1) == '-') {
                $ret -= substr($atts['math'], 1);
            }
        }
        if (array_key_exists('display', $atts) && $atts['display'] == 'percent' && is_numeric($atts['math'])) {
            $ret = number_format(100 * $atts['math'] / $ret, 0) . '%';
        }

        if (array_key_exists('display', $atts) && $atts['display'] == 'text') {
            rub\is\mjohnson\numword\Numword::$sep = ' ';
            rub\is\mjohnson\numword\Numword::$ordinal = array_key_exists('ordinalize', $atts) && !empty($atts['ordinalize']);
            $ret = rub\is\mjohnson\numword\Numword::single($ret);
        } else if (array_key_exists('ordinalize', $atts) && !empty($atts['ordinalize'])) {
            $ret .= _ordinal_suffix($ret);
        }
    }

    if (array_key_exists('display', $atts) && in_array($atts['display'], array('meter', 'cm', 'inch', 'foot', 'feet', 'ft', 'lb', 'kg', 'stone', 'abbr', 'minutes'))) {
        if ($ret == '' || $ret == null) {
            return 'N/A';
        }
        if (!is_numeric($ret)) {
            return $ret;
        }

        switch ($atts['display']) {
            case 'minutes':
                $h = floor($ret / 60);
                $m = $ret % 60;
                $ret = ($h > 0 ? $h . ' hour' . ($h > 1 ? 's' : '') . ($m > 0 ? ' ' : '') : '') . ($m > 0 || $h == 0 ? $m . ' minute' . ($m != 1 ? 's' : '') : '');
                break;
            case 'lb':
                $unit = 'lb';
                break;
            case 'inch':
                $unit = 'inch' . ($ret > 1 ? 'es' : '');
                break;
            case 'meter':
                $ret = number_format(2.54 * $ret / 100, 2);
                $unit = 'm';
                break;
            case 'cm':
                $ret = number_format(2.54 * $ret, 0);
                $unit = 'cm';
                break;
            case 'foot':
                $ret = (floor($ret / 12) > 0 ? floor($ret / 12) . (floor($ret / 12) > 1 ? ' feet' : ' foot') . ($ret % 12 > 0 ? ' ' : '') : '') . ($ret % 12 > 0 || floor($ret / 12) == 0 ? ($ret % 12) . ' inch' . ($ret % 12 == 1 ? '' : 'es') : '');
                break;
            case 'feet':
                $ret = floor($ret / 12) . '\' ' . ($ret % 12) . '"';
                break;
            case 'ft':
                $ret = (floor($ret / 12) > 0 ? floor($ret / 12) . ' ft, ' : '') . ($ret % 12) . ' in';
                break;
            case 'kg':
                $ret = number_format($ret * 0.45359237, 0);
                $unit = 'kg';
                break;
            case 'stone':
                $ret = number_format($ret / 14, 1);
                break;
            case 'abbr':
                if ($ret >= 1000000000) {
                    $ret = round($ret / 1000000000, 0) . ' billion';
                } else if ($ret >= 1000000) {
                    $ret = round($ret / 1000000, 0) . ' million';
                } else if ($ret >= 1000) {
                    $ret = round($ret / 1000, 0) . ' thousand';
                }
                break;
        }
    }

    if (array_key_exists('clean', $atts) && !empty($atts['clean'])) {
        $ret = str_replace(
            array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+', '=', '\'', '"', ',', '.', '/', '?', '\\', '|', '{', '}', '[', ']', '`', '~', ':', ';'),
            '',
            $ret
        );
    }

    if (array_key_exists('random', $atts) && !empty($atts['random'])) {
        $values = explode($atts['random'], $ret);
        $ret = $values[mt_rand(0, count($values) - 1)];
    }

    if (array_key_exists('description', $atts) && !empty($atts['description'])) {
        $ret = '';
        $options = wp_load_alloptions();
        for ($z = 0; $z < 100; $z++) {
            if (isset($options['text-morph-settings-find-' . $z]) && $options['text-morph-settings-find-' . $z] == $content) {
                $ret = isset($options['text-morph-settings-replace-' . $z]) ? nl2br($options['text-morph-settings-replace-' . $z]) : '';
                break;
            }
        }
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

    if (array_key_exists('space', $atts)) {
        $ret = str_replace(' ', $atts['space'], $ret);
    }

    return $ret . ($unit ? ' ' . $unit : '');
}

function text_morph_settings_page()
{
?>
    <div class="wrap">
        <form action="options.php" method="post">
            <h2>Text Substitutions</h2>
            <?php
            settings_fields('text-morph-settings');
            ?>
            <table class="form-table">
                <tr>
                    <td><?php submit_button(); ?></td>
                </tr>
                <tr>
                    <td><b>Match</b></td>
                    <td><b>Replace</b></td>
                </tr>
                <?php for ($z = 0; $z < 100; $z++) { ?>
                    <tr>
                        <td><textarea placeholder="" name="text-morph-settings-find-<?php echo $z; ?>" rows="4" cols="30"><?php echo esc_attr(get_option('text-morph-settings-find-' . $z)); ?></textarea></td>
                        <td><textarea placeholder="" name="text-morph-settings-replace-<?php echo $z; ?>" rows="4" cols="100"><?php echo esc_attr(get_option('text-morph-settings-replace-' . $z)); ?></textarea></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php submit_button(); ?></td>
                </tr>
            </table>
        </form>
    </div>
<?php

}

if (!function_exists("_ordinal_suffix")) {
    function _ordinal_suffix($number)
    {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');

        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return 'th';
        } else {
            return $ends[$number % 10];
        }
    }
}

// Init
add_shortcode('morph', 'morph_func');

add_action('admin_menu', function () {
    add_options_page('Text Morph Settings', 'Text Morph', 'manage_options', 'text-morph-settings', 'text_morph_settings_page');
});

add_action('admin_init', function () {
    for ($z = 0; $z < 100; $z++) {
        register_setting('text-morph-settings', 'text-morph-settings-find-' . $z);
        register_setting('text-morph-settings', 'text-morph-settings-replace-' . $z);
    }
});

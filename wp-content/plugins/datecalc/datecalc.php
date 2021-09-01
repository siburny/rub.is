<?php

/**
 * Plugin Name: dateCalc
 * Description: Flexible date and time formatter
 * Version: 4.1
 */

use function PHPSTORM_META\map;

require_once 'Numword.php';
require_once 'load_csv.php';

function number_format_nozero($nbr, $precision = 0)
{
    $nbr = number_format($nbr, $precision);
    return strpos($nbr, '.') !== false ? rtrim(rtrim($nbr, '0'), '.') : $nbr;
}

function is_leap_year($year)
{
    return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
}

function extraDays($y)
{
    // If current year is a leap year,
    // then number of weekdays move
    // ahead by 2 in terms of weekdays.
    if (
        $y % 400 == 0 ||
        $y % 100 != 0 &&
        $y % 4 == 0
    ) {
        return 2;
    }

    // Else number of weekdays
    // move ahead by 1.
    return 1;
}

// Returns next identical year.
function nextYear($y)
{
    // Find number of days
    // moved ahead by y
    $days = extraDays($y);

    // Start from next year
    $x = $y + 1;

    // Count total number of weekdays
    // moved ahead so far.
    for ($sum = 0;; $x++) {
        $sum = ($sum + extraDays($x)) % 7;

        // If sum is divisible by 7
        // and leap-ness of x is
        // same as y, return x.
        if ($sum == 0 && (extraDays($x) == $days)) {
            return $x;
        }
    }

    return $x;
}

function nl2br_str($string)
{
    return str_replace(["\r\n", "\r", "\n"], '<br/>', $string);
}

function ConvertToRoman($num)
{
    $n = intval($num);
    $res = '';

    //array of roman numbers
    $romanNumber_Array = array(
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
    );

    foreach ($romanNumber_Array as $roman => $number) {
        //divide to get  matches
        $matches = intval($n / $number);

        //assign the roman char * $matches
        $res .= str_repeat($roman, $matches);

        //substract from the number
        $n = $n % $number;
    }

    // return the result
    return $res;
}

function generate_export_json()
{
    $all_settings = get_registered_settings();
    $return = array();

    foreach ($all_settings as $key => $value) {
        if ($value['group'] == 'date-calc-settings') {
            $return[$key] = get_option($key);
        }
    }

    return json_encode($return);
}

if (!function_exists('_ordinal_suffix')) {
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

function zodiac($day, $month, $image = false)
{
    $zodiac = array('', 'Capricorn', 'Aquarius', 'Pisces', 'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn');
    $zodiacImage = array('', '&#9809;', '&#9810;', '&#9811;', '&#9800;', '&#9801;', '&#9802;', '&#9803;', '&#9804;', '&#9805;', '&#9806;', '&#9807;', '&#9808;', '&#9809;');
    $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);

    return
        $image ? (($day > $last_day[$month]) ? $zodiacImage[$month + 1] : $zodiacImage[$month])
        : (($day > $last_day[$month]) ? $zodiac[$month + 1] : $zodiac[$month]);
}

function zodiacElement($day, $month)
{
    $zodiacElement = array('', 'Earth', 'Air', 'Water', 'Fire', 'Earth', 'Air', 'Water', 'Fire', 'Earth', 'Air', 'Water', 'Fire', 'Earth');
    $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);

    return ($day > $last_day[$month]) ? $zodiacElement[$month + 1] : $zodiacElement[$month];
}

function zodiacLuckyDay($day, $month)
{
    $luckyDay = array('', 'Saturday', 'Wednesday', 'Thursday', 'Tuesday', 'Friday', 'Wednesday', 'Monday', 'Sunday', 'Wednesday', 'Friday', 'Tuesday', 'Thursday', 'Saturday');
    $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);

    return ($day > $last_day[$month]) ? $luckyDay[$month + 1] : $luckyDay[$month];
}

function zodiacSpiritAnimal($day, $month)
{
    $spiritAnimal = array('', 'Goose', 'Otter', 'Wolf', 'Hawk', 'Beaver', 'Deer', 'Woodpecker', 'Salmon', 'Bear', 'Raven', 'Snake', 'Owl', 'Goose');
    $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);

    return ($day > $last_day[$month]) ? $spiritAnimal[$month + 1] : $spiritAnimal[$month];
}

function zodiacQuality($day, $month)
{
    $zodiacQuality = array('', 'Cardinal', 'Fixed', 'Mutable', 'Cardinal', 'Fixed', 'Mutable', 'Cardinal', 'Fixed', 'Mutable', 'Cardinal', 'Fixed', 'Mutable', 'Cardinal');
    $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);

    return ($day > $last_day[$month]) ? $zodiacQuality[$month + 1] : $zodiacQuality[$month];
}

function zodiacColor($day, $month, $icon = false)
{
    $zodiacColorName = array('', 'Grey', 'Blue', 'Light Green', 'Red', 'Green', 'Yellow', 'Silver', 'Orange', 'Brown', 'Pink', 'Black', 'Purple', 'Grey');
    $zodiacColorCode = array('', '808080', '0000FF', '90EE90', 'FF0000', '00FF00', 'FFFF00', 'C0C0C0', 'FFA500', 'A52A2A', 'FFC0CB', '000000', '800080', '808080');
    $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);

    if ($icon) {
        $color = '';
        if ($day > $last_day[$month]) {
            $color = $zodiacColorCode[$month + 1];
        } else {
            $color = $zodiacColorCode[$month];
        }

        return '<span style="color:#' . $color . '">&#x2B24;</span>';
    } else {
        return (($day > $last_day[$month]) ? $zodiacColorName[$month + 1] : $zodiacColorName[$month]);
    }
}

function datecalc_func($atts)
{
    global $all_data;

    $prefix = '';
    $date = new DateTime('now', new DateTimeZone(get_option('timezone_string')));

    if (empty($atts)) {
        $atts = array();
    }

    if (array_key_exists('until', $atts)) {
        $date = new DateTime($atts['until'], new DateTimeZone(get_option('timezone_string')));
        try {
            $date = new DateTime($atts['until'], new DateTimeZone(get_option('timezone_string')));
        } catch (Exception $e) {
            return $atts['until'];
        }
    }

    if (array_key_exists('date', $atts)) {
        if (is_numeric($atts['date'])) {
            $atts['date'] = $atts['date'] . '/1/1';
        }
        try {
            $date = new DateTime($atts['date'], new DateTimeZone(get_option('timezone_string')));
        } catch (Exception $e) {
            return $atts['date'];
        }
    }

    if (array_key_exists('now', $atts)) {
        $now = new DateTime('now', new DateTimeZone(get_option('timezone_string')));
        if ($now > $date) {
            $date->setDate($now->format('Y'), $date->format('m'), $date->format('d'));
            if ($now > $date) {
                $date->setDate($now->format('Y') + 1, $date->format('m'), $date->format('d'));
            }
        }
    }

    $now_override = 'now';
    if (array_key_exists('now_override', $atts)) {
        $now_override = $atts['now_override'];
        //print $now_override;
    }

    // TODO: delete eventually
    if (array_key_exists('add', $atts)) {
        if (is_numeric($atts['add'])) {
            if ($atts['add'] > 0) {
                $date->add(new DateInterval('P' . abs($atts['add']) . 'Y'));
            } else {
                $date->sub(new DateInterval('P' . abs($atts['add']) . 'Y'));
            }
        }
    }

    if (array_key_exists('hour', $atts)) {
        if (is_numeric($atts['hour'])) {
            if ($atts['hour'] > 0) {
                $date->add(new DateInterval('PT' . abs($atts['hour']) . 'H'));
            } else {
                $date->sub(new DateInterval('PT' . abs($atts['hour']) . 'H'));
            }
        } else if (strpos($atts['hour'], ':') != false) {
            $int = explode(':', $atts['hour']);

            $int_str = 'PT' . abs($int[0]) . 'H';
            if (!empty($int[1])) {
                $int_str .= $int[1] . 'M';
            }
            if (!empty($int[2])) {
                $int_str .= $int[2] . 'S';
            }

            if ($int[0] > 0) {
                $date->add(new DateInterval($int_str));
            } else {
                $date->sub(new DateInterval($int_str));
            }
        }
    }

    if (array_key_exists('day', $atts)) {
        if (is_numeric($atts['day'])) {
            if ($atts['day'] > 0) {
                $date->add(new DateInterval('P' . abs($atts['day']) . 'D'));
            } else {
                $date->sub(new DateInterval('P' . abs($atts['day']) . 'D'));
            }
        }
    }

    if (array_key_exists('month', $atts)) {
        if (is_numeric($atts['month'])) {
            if ($atts['month'] > 0) {
                $date->add(new DateInterval('P' . abs($atts['month']) . 'M'));
            } else {
                $date->sub(new DateInterval('P' . abs($atts['month']) . 'M'));
            }
        }
    }

    if (array_key_exists('year', $atts)) {
        if (is_numeric($atts['year'])) {
            if ($atts['year'] > 0) {
                $date->add(new DateInterval('P' . abs($atts['year']) . 'Y'));
            } else {
                $date->sub(new DateInterval('P' . abs($atts['year']) . 'Y'));
            }
        }
    }

    if (array_key_exists('option', $atts)) {
        $tz = $atts['option'];
        if (strtolower(substr($tz, 0, 3)) == "gmt") {
            $tz = substr($tz, 3) . '00';
        }
        $date->setTimezone(new DateTimeZone($tz));
    }

    if (empty($date)) {
        return '';
    }

    $display = 'yyyy';
    if (array_key_exists('display', $atts)) {
        $display = $atts['display'];
    }

    $ordinalize = false;
    if (array_key_exists('ordinalize', $atts) && ($atts['ordinalize'] == 'yes' || $atts['ordinalize'] == '1' || $atts['ordinalize'] == 'true')) {
        $ordinalize = true;
    }

    $description = false;
    if (array_key_exists('description', $atts) && ($atts['description'] == 'yes' || $atts['description'] == '1' || $atts['description'] == 'true')) {
        $description = true;
    }

    if (array_key_exists('age', $atts) && ($atts['age'] == 'yes' || $atts['age'] == '1' || $atts['age'] == 'true')) {
        $diff = date_diff($date, date_create($now_override, new DateTimeZone(get_option('timezone_string'))));
        $ret = $diff->format('%y');

        if ($ret == 0) {
            $ret = $diff->format('%m');

            if ($ret > 0) {
                return $ret . ' month' . ($ret > 1 ? 's' : '');
            } else {
                $ret = $diff->format('%d');
                return $ret . ' day' . ($ret > 1 ? 's' : '');
            }
        }
    } else if (array_key_exists('dogyears', $atts) && ($atts['dogyears'] == 'yes' || $atts['dogyears'] == '1' || $atts['dogyears'] == 'true')) {
        $diff = date_diff($date, date_create($now_override, new DateTimeZone(get_option('timezone_string'))));
        $ret = floor(($diff->format('%y') - 14) / 4.731136854);
    } else if (array_key_exists('fullage', $atts) && ($atts['fullage'] == 'yes' || $atts['fullage'] == '1' || $atts['fullage'] == 'true')) {
        $diff = date_diff($date, date_create($now_override, new DateTimeZone(get_option('timezone_string'))));
        $ret = $diff->format('%y');
        mjohnson\numword\Numword::$sep = ' ';
        $ret = mjohnson\numword\Numword::single($ret);
    } else if (array_key_exists('roman', $atts) && ($atts['roman'] == 'yes' || $atts['roman'] == '1' || $atts['roman'] == 'true')) {
        if (array_key_exists('display', $atts)) {
            $ret = $atts['display'];
        } else {
            $ret = 'mmddyyyy';
        }

        $ret = str_replace('yyyy', ConvertToRoman($date->format('Y')), $ret);
        $ret = str_replace('mm', ConvertToRoman($date->format('n')), $ret);
        $ret = str_replace('dd', ConvertToRoman($date->format('j')), $ret);
        $ret = str_replace('m', ConvertToRoman($date->format('n')), $ret);
        $ret = str_replace('d', ConvertToRoman($date->format('j')), $ret);

        return $ret;
    } else if (array_key_exists('zodiac', $atts) && ($atts['zodiac'] == 'yes' || $atts['zodiac'] == '1' || $atts['zodiac'] == 'true')) {
        if (array_key_exists('icon', $atts) && !empty($atts['icon'])) {
            $ret = zodiac($date->format('j'), $date->format('n'), true);
        } else {
            $ret = zodiac($date->format('j'), $date->format('n'));
            $ret = $description ? nl2br_str(get_option('date-calc-zodiac-' . strtolower($ret))) : $ret;
        }
    } else if (array_key_exists('powercolor', $atts) && ($atts['powercolor'] == 'yes' || $atts['powercolor'] == '1' || $atts['powercolor'] == 'true')) {
        $ret = zodiacColor($date->format('j'), $date->format('n'), array_key_exists('icon', $atts) && !empty($atts['icon']));
    } else if (array_key_exists('element', $atts) && ($atts['element'] == 'yes' || $atts['element'] == '1' || $atts['element'] == 'true')) {
        $ret = zodiacElement($date->format('j'), $date->format('n'));

        if (array_key_exists('icon', $atts)) {
            $icons = array(
                'Fire' => '🔥',
                'Earth' => '🌎',
                'Air' => '💨',
                'Water' => '🌊'
            );
            return $icons[$ret];
        }

        $ret = $description ? nl2br_str(get_option('date-calc-zodiacelement-' . strtolower($ret))) : $ret;
    } else if (array_key_exists('spiritanimal', $atts) && ($atts['spiritanimal'] == 'yes' || $atts['spiritanimal'] == '1' || $atts['spiritanimal'] == 'true')) {
        $ret = zodiacSpiritAnimal($date->format('j'), $date->format('n'));

        $ret = $description ? nl2br_str(get_option('date-calc-zodiacspiritanimal-' . strtolower($ret))) : $ret;
    } else if (array_key_exists('luckyday', $atts) && ($atts['luckyday'] == 'yes' || $atts['luckyday'] == '1' || $atts['luckyday'] == 'true')) {
        $ret = zodiacLuckyDay($date->format('j'), $date->format('n'));

        $ret = $description ? nl2br_str(get_option('date-calc-zodiacluckyday-' . strtolower($ret))) : $ret;
    } else if (array_key_exists('quality', $atts) && ($atts['quality'] == 'yes' || $atts['quality'] == '1' || $atts['quality'] == 'true')) {
        $ret = zodiacQuality($date->format('j'), $date->format('n'));
        $ret = $description ? nl2br_str(get_option('date-calc-zodiacquality-' . strtolower($ret))) : $ret;
    } else if (array_key_exists('chinesezodiac', $atts) && ($atts['chinesezodiac'] == 'yes' || $atts['chinesezodiac'] == '1' || $atts['chinesezodiac'] == 'true')) {
        $chineseZodiac = array('Monkey', 'Rooster', 'Dog', 'Pig', 'Rat', 'Ox', 'Tiger', 'Rabbit', 'Dragon', 'Serpent', 'Horse', 'Goat');
        $icons = array('🐒', '🐓', '🐕', '🐖', '🐀', '🐂', '🐅', '🐇', '🐉', '🐍', '🐎', '🐐');

        if (array_key_exists('icon', $atts)) {
            return $icons[$date->format('Y') % 12];
        }

        $ret = $chineseZodiac[$date->format('Y') % 12];
        $ret = $description ? nl2br_str(get_option('date-calc-chinesezodiac-' . strtolower($ret)) . "\n\n") : $ret;
    } else if (array_key_exists('flower', $atts) && ($atts['flower'] == 'yes' || $atts['flower'] == '1' || $atts['flower'] == 'true')) {
        $flower = array('Carnation', 'Violet', 'Daffodil', 'Sweet Pea/Daisy', 'Lily of the valley', 'Rose', 'Larkspur', 'Gladiolus', 'Aster/Myosotis', 'Marigold', 'Chrysanthemum', 'Narcissus');
        $ret = $flower[$date->format('n') - 1];
        $ret = $description ? nl2br_str(get_option('date-calc-flower-' . str_replace(array('/', ' ', ','), '-', strtolower($ret)))) : $ret;
    } else if (array_key_exists('stone', $atts) && ($atts['stone'] == 'yes' || $atts['stone'] == '1' || $atts['stone'] == 'true')) {
        $stone = array('Garnet', 'Amethyst', 'Aquamarine', 'Diamond', 'Emerald', 'Pearl, Moonstone and Alexandrite', 'Ruby', 'Peridot and Sardonyx', 'Sapphire', 'Opal and Tourmaline', 'Topaz and Citrine', 'Tanzanite, Turquoise, Zircon and Topaz');
        $ret = $stone[$date->format('n') - 1];
        $ret = $description ? nl2br_str(get_option('date-calc-stone-' . str_replace(array('/', ' ', ','), '-', strtolower($ret)))) : $ret;
    } else if (array_key_exists('generation', $atts) && ($atts['generation'] == 'yes' || $atts['generation'] == '1' || $atts['generation'] == 'true')) {
        $year = $date->format('Y');
        if ($year <= 1924) {
            $ret = 'G.I. Generation';
        } else if ($year <= 1942) {
            $ret = 'Silent Generation';
        } else if ($year <= 1964) {
            $ret = 'Baby Boomers Generation';
        } else if ($year <= 1979) {
            $ret = 'Generation X';
        } else if ($year <= 2000) {
            $ret = 'Millennials Generation';
        } else if ($year <= 2009) {
            $ret = 'Generation Z';
        } else {
            $ret = 'Generation Alpha';
        }
        $generation = array('G.I. Generation' => 'gi-generation', 'Silent Generation' => 'silent-generation', 'Baby Boomers Generation' => 'baby-boomers', 'Generation X' => 'generation-x', 'Millennials Generation' => 'millennials', 'Generation Z' => 'generation-z', 'Generation Alpha' => 'generation-alpha');
        $ret = $description ? nl2br_str(get_option('date-calc-generation-' . $generation[$ret])) : $ret;
    } else if (array_key_exists('decade', $atts) && ($atts['decade'] == 'yes' || $atts['decade'] == '1' || $atts['decade'] == 'true')) {
        $ret = intval($date->format('Y') / 10) * 10;
        $ret .= 's';
        $ret = $description ? nl2br_str(get_option('date-calc-decade-' . $ret)) : $ret;
    } else if (array_key_exists('samecalendar', $atts) && ($atts['samecalendar'] == 'yes' || $atts['samecalendar'] == '1' || $atts['samecalendar'] == 'true')) {
        $ret = $date->format('Y');
        while (($ret = nextYear($ret)) < date('Y'));
        $ret = $ret;
    } else if (array_key_exists('song', $atts) && ($atts['song'] == 'yes' || $atts['song'] == '1' || $atts['song'] == 'true')) {
        $key = $date->format('n/j/Y');
        if (array_key_exists($key, $all_data['song'])) {
            if (array_key_exists('youtube', $atts)) {
                return '<iframe src="https://www.youtube.com/embed?listType=search&amp;list=' . urlencode($all_data['song'][$key][0]['Artist'] . ' - ' . $all_data['billboard'][$key][0]['Song']) . '" frameborder="0"></iframe>';
            } else {
                return '&quot;' . $all_data['song'][$key][0]['song'] . '&quot; by ' . $all_data['song'][$key][0]['artist'];
            }
        }
        return '[Not available]. No song matches found.';
    } else if (array_key_exists('planet', $atts) && ($atts['planet'] == 'yes' || $atts['planet'] == '1' || $atts['planet'] == 'true')) {
        if (!function_exists('planet')) {
            function planet($day, $month)
            {
                $planet = array('', 'Saturn', 'Uranus', 'Neptune', 'Mars', 'Venus', 'Mercury', 'Moon', 'Sun', 'Mercury', 'Venus', 'Pluto', 'Jupiter', 'Saturn');
                $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);
                return ($day > $last_day[$month]) ? $planet[$month + 1] : $planet[$month];
            }
        }
        $ret = planet($date->format('j'), $date->format('n'));

        if (array_key_exists('icon', $atts)) {
            $icons = array('Sun' => '☉', 'Moon' => '☽', 'Mercury' => '☿', 'Venus' => '♀', 'Mars' => '♂', 'Jupiter' => '♃', 'Saturn' => '♄', 'Uranus' => '♅', 'Neptune' => '♆', 'Pluto' => '♇');
            return $icons[$ret];
        }

        $ret = $description ? nl2br_str(get_option('date-calc-planet-' . strtolower($ret))) : $ret;
    } else if (array_key_exists('babyname', $atts) || array_key_exists('babynames', $atts)) {
        $count = array_key_exists('numbers', $atts);

        if (!array_key_exists($date->format('Y'), $all_data['babyname'])) {
            return '';
        }
        $ret = $all_data['babyname'][$date->format('Y')];

        if ($count) {
            if ($display == 'girl') {
                return number_format_nozero(str_replace(',', '', $ret[0]['girlnumber']));
            } else if ($display == 'boy') {
                return number_format_nozero(str_replace(',', '', $ret[0]['boynumbers']));
            }
        } else {
            if ($display == 'girl') {
                return str_replace(',', '', $ret[0]['girl']);
            } else if ($display == 'boy') {
                return str_replace(',', '', $ret[0]['boy']);
            }
        }
        return '';
    } else if (array_key_exists('birthday', $atts) || array_key_exists('birthdays', $atts)) {
        if (!array_key_exists($date->format('n/j/Y'), $all_data['birthday'])) {
            return 'We could not find a celebrity that shares a birthday with you. However, we update our database constantly, and invite you to check back later to see if we found someone with the same birthday.';
        }
        $ret = $all_data['birthday'][$date->format('n/j/Y')];

        $count = 3;
        if (!array_key_exists('show', $atts) && is_numeric($atts['show'])) {
            $count = 0 + $atts['show'];
        }

        if (array_key_exists('order', $atts) && $atts['order'] == 'new') {
            $ret = array_reverse($ret);
        }

        $str = array();
        $i = 0;
        foreach ($ret as $key => $value) {
            if ($i++ < $count) {
                $str[] = $value['name'] . ' (' . $value['profession'] . ')';
            }
        }

        return 'You share a birthday with ' . implode(', ', $str);
    } else if (array_key_exists('event', $atts) || array_key_exists('events', $atts)) {
        if (!array_key_exists($date->format('n/j'), $all_data['event'])) {
            return '';
        }
        $ret = $all_data['event'][$date->format('n/j')];

        $count = 3;
        if (!array_key_exists('show', $atts) && is_numeric($atts['show'])) {
            $count = 0 + $atts['show'];
        }

        if (array_key_exists('order', $atts) && $atts['order'] == 'old') {
            ksort($ret);
        } else {
            krsort($ret);
        }

        $str = '';
        $i = 0;
        foreach ($ret as $key => $value) {
            if ($i++ < $count) {
                $str .= '<p><b>' . $value['year'] . '</b> &ndash; ' . $value['event'] . '</p>';
            }
        }

        return $str;
    } else if (array_key_exists('president', $atts) || array_key_exists('presidents', $atts)) {
        $array = array();
        if (!empty($all_data['president'])) {
            $array = $all_data['president'];
        } elseif (!empty($all_data['presidents'])) {
            $array = $all_data['presidents'];
        }

        foreach ($array as $key => $val) {
            $val = $val[0];
            if (new DateTime($val['took office']) <= $date && ($val['left office'] == 'Incumbent' || new DateTime($val['left office']) > $date)) {
                return $val['president'] . ' (' . $val['party'] . ')';
            }
        }

        return '';
    } else if (array_key_exists('holiday', $atts) || array_key_exists('holidays', $atts)) {
        $type = 'all';
        if (array_key_exists('type', $atts)) {
            $type = $atts['type'];
        }

        $country = 'all';
        if (array_key_exists('country', $atts) && (strlen($atts['country']) == 2 || $atts['country'] == 'all')) {
            $country = strtolower($atts['country']);
        }

        $all = get_all_holidays($date->format('Y'));
        if (!$all) {
            return 'No major holidays found for this date.';
        }

        $search_date = $date->format('n/j');
        $ret = array();
        foreach ($all[$search_date] as $h) {
            if (($h['type'] == $type || $type == 'all') && ($country == 'all' || $h['country'] == $country)) {
                $ret[] = $h;
            }
        }

        if (count($ret) == 1) {
            return $description ? $ret[0]['description'] : $ret[0]['holiday'];
        } else if (count($ret) > 1) {
            $ret = $description ? array_column($ret, 'description') : array_column($ret, 'holiday');

            $count = 3;
            if (array_key_exists('show', $atts) && is_numeric($atts['show'])) {
                $count = 0 + $atts['show'];
            }

            $ret = array_slice($ret, 0, $count);
            return '<ul><li>' . implode('</li><li>', $ret) . '</li></ul>';
        }

        return 'No major holidays found for this date.';
    } else if (array_key_exists('difference', $atts) && $atts['difference'] != 'true') {
        $doPlural = function ($nb, $str) {
            return $nb > 1 ? $str . 's' : $str;
        };

        try {
            $date_diff = new DateTime($atts['difference'], new DateTimeZone(get_option('timezone_string')));
            $diff = $date_diff->diff($date);
        } catch (Exception $e) {
            $diff = new DateInterval('PT0S');
        }

        $ago = '';
        if ($diff->invert) {
            $ago = ' ago';
        }

        if (!array_key_exists('display', $atts)) {
            if ($diff->y > 1 || ($diff->y == 1 && $diff->m == 0)) { // YEAR
                return $diff->y . ' ' . $doPlural($diff->y, 'year') . $ago;
            } else if ($diff->y > 0) {
                return $diff->y . ' ' . $doPlural($diff->y, 'year') . ' and ' . $diff->m . ' ' . $doPlural($diff->m, 'month') . $ago;
            } else if ($diff->m > 1) { // MONTH
                return $diff->m . ' ' . $doPlural($diff->m, 'month') . $ago;
            } else if ($diff->m > 0) {
                return $diff->m . ' ' . $doPlural($diff->y, 'month') . ' and ' . $diff->d . ' ' . $doPlural($diff->d, 'day') . $ago;
            } else if ($diff->d > 1) { // DAY
                return $diff->d . ' ' . $doPlural($diff->d, 'day') . $ago;
            } else if ($diff->d > 0) {
                return $diff->d . ' ' . $doPlural($diff->d, 'month') . ' and ' . $diff->h . ' ' . $doPlural($diff->h, 'day') . $ago;
            } else if ($diff->h > 1) { // HOUR
                return $diff->h . ' ' . $doPlural($diff->h, 'hour') . $ago;
            } else if ($diff->h > 0) {
                return $diff->h . ' ' . $doPlural($diff->h, 'hour') . ' and ' . $diff->i . ' ' . $doPlural($diff->i, 'minute') . $ago;
            } else if ($diff->d > 1) { // MINUTE
                return $diff->d . ' ' . $doPlural($diff->i, 'minute') . $ago;
            } else if ($diff->d > 0) {
                return $diff->d . ' ' . $doPlural($diff->i, 'minute') . ' and ' . $diff->s . ' ' . $doPlural($diff->s, 'second') . $ago;
            }
        } else if ($display == 'year') {
            $ret = number_format_nozero($diff->format('%y'));
            return $ret . ' ' . $doPlural($ret, 'year');
        } else if ($display == 'month') {
            $ret = number_format_nozero($diff->format('%m') + 12 * $diff->format('%y'));
            return $ret . ' ' . $doPlural($ret, 'month');
        } else if ($display == 'week') {
            $ret = number_format_nozero(round($diff->format('%a') / 7));
            return $ret . ' ' . $doPlural($ret, 'week');
        } else if ($display == 'day') {
            $ret = number_format_nozero($diff->format('%a'));
            return $ret . ' ' . $doPlural($ret, 'day');
        } else if ($display == 'hour') {
            $ret = number_format_nozero($diff->format('%a') * 24 + $diff->h);
            return $ret . ' ' . $doPlural($ret, 'hour');
        } else if ($display == 'minute') {
            $ret = number_format_nozero(($diff->format('%a') * 24 + $diff->h) * 60 + $diff->i);
            return $ret . ' ' . $doPlural($ret, 'minute');
        } else if ($display == 'second') {
            $ret = number_format_nozero((($diff->format('%a') * 24 + $diff->h) * 60 + $diff->i) * 60 + $diff->s);
            return $ret . ' ' . $doPlural($ret, 'second');
        } else if ($display == 'full' || $display == 'age') {

            if ($diff->y !== 0) {
                $format[] = '%y ' . $doPlural($diff->y, 'year');
            }
            if ($diff->m !== 0) {
                $format[] = '%m ' . $doPlural($diff->m, 'month');
            }
            if ($diff->d !== 0) {
                $format[] = '%d ' . $doPlural($diff->d, 'day');
            }

            if (count($format) > 1) {
                $format = implode(', ', $format);
            } else {
                $format = array_pop($format);
            }

            if (strpos($format, ',') !== false) {
                $pos = strrpos($format, ',');

                if ($pos !== false) {
                    $format = substr_replace($format, ', and', $pos, 1);
                }
            }

            if ($display == 'age') {
                if (!$diff->invert) {
                    $format .= ' younger';
                } else {
                    $format .= ' older';
                }
            }

            return $diff->format($format);
        }

        return '';
    } else if (array_key_exists('leap', $atts) && ($atts['leap'] == 'yes' || $atts['leap'] == '1' || $atts['leap'] == 'true')) {
        $count = array_key_exists('count', $atts);
        $leap = $date->format('L');

        if ($leap) {
            if ($count) {
                $ret = '366';
            } else {
                $ret = 'leap year';
            }
        } else {
            if ($count) {
                $ret = '365';
            } else {
                $ret = 'not a leap year';
            }
        }
    } else if (array_key_exists('vitals', $atts) && ($atts['vitals'] == 'yes' || $atts['vitals'] == '1' || $atts['vitals'] == 'true')) {
        $diff = date_diff($date, date_create($now_override, new DateTimeZone(get_option('timezone_string'))))->days;

        if (array_key_exists('display', $atts)) {
            if ($atts['display'] == 'heartbeats') {
                $ret = number_format($diff * 24 * 60 * 80);
            }
            if ($atts['display'] == 'blinks') {
                $ret = number_format($diff * 24 * 60 * 17);
            }
            if ($atts['display'] == 'steps') {
                $ret = number_format($diff * 24 * 7000);
            }
            if ($atts['display'] == 'breaths') {
                $ret = number_format($diff * 24 * 60 * 14);
            }
        }
    } else if (array_key_exists('earthtravel', $atts) && ($atts['earthtravel'] == 'yes' || $atts['earthtravel'] == '1' || $atts['earthtravel'] == 'true')) {
        $diff = date_diff($date, date_create($now_override, new DateTimeZone(get_option('timezone_string'))))->days;

        $ret = number_format($diff * 1600000) . ' miles';
    } else if (array_key_exists('sleep', $atts)) {
        $doPlural = function ($nb, $str) {
            return $nb > 1 ? $str . 's' : $str;
        };

        $date_diff = new DateTime($now_override, new DateTimeZone(get_option('timezone_string')));
        $diff = $date_diff->diff($date);

        if ($display == 'year') {
            return number_format_nozero($diff->format('%a') / 365 / 3, 2);
        } else if ($display == 'month') {
            return number_format_nozero(($diff->format('%m') + 12 * $diff->format('%y')) / 3);
        } else if ($display == 'week') {
            return number_format_nozero((round($diff->format('%a') / 7)) / 3);
        } else if ($display == 'day') {
            return number_format_nozero(($diff->format('%a')) / 3);
        } else if ($display == 'hour') {
            return number_format_nozero(($diff->format('%a') * 24 + $diff->h) / 3);
        } else if ($display == 'minute') {
            return number_format_nozero((($diff->format('%a') * 24 + $diff->h) * 60 + $diff->i) / 3);
        } else if ($display == 'second') {
            return number_format_nozero(((($diff->format('%a') * 24 + $diff->h) * 60 + $diff->i) * 60 + $diff->s) / 3);
        }

        return '';
    } else if (array_key_exists('milestone', $atts)) {
        if ($display == 'day') {
            $date->add(new DateInterval('P10000D'));
        } else if ($display == 'hour') {
            $date->add(new DateInterval('PT100000H'));
        } else if ($display == 'second') {
            $date->add(new DateInterval('PT1000000000S'));
        }

        $now = new DateTime($now_override, new DateTimeZone(get_option('timezone_string')));
        if ($date > $now) {
            return 'will happen sometime on ' . $date->format('F j, Y');
        } else {
            return 'was on ' . $date->format('F j, Y');
        }
    } else if (array_key_exists('moon', $atts)) {
        $date_diff = new DateTime($now_override, new DateTimeZone(get_option('timezone_string')));
        $diff = $date_diff->diff($date);

        return number_format_nozero(1.0 * $diff->format('%a') / 29.5);
    } else if (array_key_exists('population', $atts)) {
        if (array_key_exists($date->format('Y'), $all_data['population'])) {
            if (array_key_exists('display', $atts) && $atts['display'] == 'abbr') {
                $ret = $all_data['population'][$date->format('Y')][0]['output'];

                if ($ret >= 1000000000) {
                    $ret = round($ret / 1000000000, 2) . ' billion';
                } else if ($ret >= 1000000) {
                    $ret = round($ret / 1000000, 2) . ' million';
                } else if ($ret >= 1000) {
                    $ret = round($ret / 1000, 2) . ' thousand';
                }

                return $ret;
            } else {
                return number_format_nozero($all_data['population'][$date->format('Y')][0]['output']);
            }
        }

        return '';
    } else if (array_key_exists('lifepath', $atts)) {
        $lifepath = function ($n) {
            $n = '' . $n;
            $r = 0;
            for ($i = 0; $i < strlen($n); $i++) {
                $r += $n[$i] - '0';
            }
            return $r;
        };

        $ret = $date->format('jnY');
        for ($i = 0; $i < 4; $i++) {
            $ret = $lifepath($ret);
            if ($ret < 10 || $ret == 11 || $ret == 22) {
                return $description ? get_option('date-calc-lifepath-' . $ret) : $ret;
            }
        }

        return '';
    } else if ($display == 'full') {
        $f1 = new NumberFormatter('en', NumberFormatter::SPELLOUT);

        $f2 = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $f2->setTextAttribute(NumberFormatter::DEFAULT_RULESET, '%spellout- nal');

        $ret = $date->format('F') . ' ' . $f2->format($date->format('j')) . ', ' . $f1->format(substr($date->format('Y'), 0, 2)) . ' ' . $f1->format(substr($date->format('Y'), 2, 2));
    } else if (count($atts) == 2 && !array_key_exists('display', $atts)) {

        // DYNAMIC CSV

        $diff = array_values(array_intersect(array_keys($atts), array_keys($all_data)));
        if (empty($diff)) {
            foreach ($atts as $a => $v) {
                if (substr($a, -1, 1) == 's' && substr($a, -2, 1) != 's') {
                    $atts[substr($a, 0, -1)] = $v;
                }
            }
            $diff = array_values(array_intersect(array_keys($atts), array_keys($all_data)));
        }

        $name = $diff[0];

        if (!isset($all_data[$name])) {
            return '';
        }

        $ret = null;
        if (array_key_exists($date->format('Y'), $all_data[$name])) {
            $ret = $all_data[$name][$date->format('Y')];
        } else if (array_key_exists($date->format('n/j/Y'), $all_data[$name])) {
            $ret = $all_data[$name][$date->format('n/j/Y')];
        } else {
            return '';
        }

        if (empty($ret)) {
            return '';
        }

        if (count($ret) == 1) {
            $ret = $ret[0];
        }

        if (array_key_exists('output', $ret)) {
            return $ret['output'];
        } else if (array_key_exists('name', $ret)) {
            return $ret['name'];
        } else {
            return '';
        }
    } else {
        $count = array_key_exists('count', $atts) && ($atts['count'] == 'yes' || $atts['count'] == '1' || $atts['count'] == 'true');

        $display = preg_split("/(yyyy|yy|mmmm|mmm|mm|m|dddd|ddd|dd|d|hh:mm|h:mm|h:m|AM\/PM|AMPM|week|w|s|text|q)/", $display, -1, PREG_SPLIT_DELIM_CAPTURE);
        $replace = array(
            'yyyy' => 'Y',
            'yy' => 'y',
            'mmmm' => 'F',
            'mmm' => 'M',
            'mm' => 'm',
            'm' => 'n',
            'dddd' => $count ? 'z' : 'l',
            'ddd' => 'D',
            'dd' => 'd' . ($ordinalize ? 'S' : ''),
            'd' => $count ? 'z' : 'j' . ($ordinalize ? 'S' : ''),
            'h:m' => 'g A',
            'h:mm' => 'g:i A',
            'hh:mm' => 'G:i',
            'AM/PM' => 'A',
            'AMPM' => 'A',
            'text' => '',
            'week' => 'z',
            'w' => 'z'
        );

        $ret = '';
        foreach ($display as $token) {
            if (array_key_exists($token, $replace)) {
                if ($replace[$token] == 'z') {
                    $date->add(new DateInterval('P1D'));
                }

                $ret .= $date->format($replace[$token]);

                if ($token == 'w' || $token == 'week') {
                    $ret = 1 + intval($ret / 7);
                    $prefix = 'Week ';
                } else if ($token == 'dddd' && $count) {
                    $ret = (1 * $ret) . _ordinal_suffix($ret) . ' ' . $date->format('l');
                }
            } elseif ($token == 's') {

                $difference = array_key_exists('difference', $atts) && ($atts['difference'] == 'yes' || $atts['difference'] == '1' || $atts['difference'] == 'true');

                $month = $date->format('n');
                if ($count) {
                    if ($difference) {
                        $diff = clone $date;

                        $q = 3 - $month % 3;
                        $diff->modify($q . ' months');
                        $diff->modify('first day of');

                        $r = $diff->diff($date);

                        $ret .= $r->days;
                        $month = ($month + 3 + 12 - 1) % 12 + 1;

                        $ret .= ' day' . ($r->days == 1 ? '' : 's') . ' left until ';
                    } else {
                        $diff = clone $date;

                        $q = -$month % 3;
                        $diff->modify($q . ' months');
                        $diff->modify('first day of');

                        $r = $date->diff($diff);

                        if ($ordinalize) {
                            $ret .= ($r->days + 1) . _ordinal_suffix($r->days + 1);
                        } else {
                            $ret .= ($r->days + 1);
                        }

                        $ret .= ' day of ';
                    }
                }

                switch ($month) {
                    case 1:
                    case 2:
                    case 12:
                        $ret .= 'Winter';
                        break;
                    case 3:
                    case 4:
                    case 5:
                        $ret .= 'Spring';
                        break;
                    case 6:
                    case 7:
                    case 8:
                        $ret .= 'Summer';
                        break;
                    case 9:
                    case 10:
                    case 11:
                        $ret .= 'Fall (autumn)';
                        break;
                }
            } else if ($token == 'q') {
                $q = ceil($date->format('n') / 3);

                if ($atts['display'] == 'q') {
                    return $q . _ordinal_suffix($q) . ' quarter';
                } else if ($atts['display'] == 'q text') {
                    $format = NumberFormatter::SPELLOUT;
                    $f = new NumberFormatter("en", $format);
                    $f->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

                    return $f->format($q) . ' quarter';
                } else if ($atts['display'] == 'q abbr') {
                    return 'Q' . $q;
                } else {
                    return $token;
                }
            } else {
                $ret .= $token;
            }
        }

        $ret = trim($ret);

        if ($description) {
            $ret = get_option('date-calc-date-' . $ret);
        }
    }

    if (array_key_exists('display', $atts) && is_numeric($ret) && strpos($atts['display'], 'text')  !== false) {
        $format = NumberFormatter::SPELLOUT;

        $f = new NumberFormatter("en", $format);
        if ($ordinalize) {
            $f->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");
        }

        $ret = str_replace('-', ' ', $f->format($ret));
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

    if ($ordinalize && is_numeric($ret)) {
        $ret .= _ordinal_suffix($ret);
    }

    $ret = $prefix . $ret;

    return $ret;
}

function get_all_holidays($year)
{
    global $all_data;

    $ret = array();

    foreach ($all_data['holiday'] as $h) {
        $date = $h[0]['date'];
        if (strpos($date, '/') !== false) {
            $date .= '/' . $year;
        } else {
            $date .= ' ' . $year;
        }

        $date = strtotime($date);
        if ($date) {
            $key = date('n/j', $date);
            //$h['date'] = $key;
            $ret[$key] = $h;
        }
    }

    return $ret;
}

function date_calc_import()
{
    if (!empty($_POST['import-data'])) {
        $data = json_decode(stripslashes($_POST['import-data']), true);

        foreach ($data as $key => $value) {
            if (substr($key, 0, 10) == 'date-calc-') {
                update_option($key, $value);
            }
        }
    }

    wp_redirect(admin_url('options-general.php?page=date-calc-settings&tab=exportimport&complete=' . time()));
    exit;
}

function date_calc_settings_page()
{
    global $all_data;

    if (isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];
    } else {
        $active_tab = 'general';
    }
?>

    <h2>dateCalc Settings</h2>

    <h2 class="nav-tab-wrapper">
        <a href="?page=date-calc-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
        <a href="?page=date-calc-settings&tab=exportimport" class="nav-tab <?php echo $active_tab == 'exportimport' ? 'nav-tab-active' : ''; ?>">Export / Import</a>
        <a href="?page=date-calc-settings&tab=zodiac" class="nav-tab <?php echo $active_tab == 'zodiac' ? 'nav-tab-active' : ''; ?>">Zodiac</a>
        <a href="?page=date-calc-settings&tab=zodiacelement" class="nav-tab <?php echo $active_tab == 'zodiacelement' ? 'nav-tab-active' : ''; ?>">Zodiac Elements</a>
        <a href="?page=date-calc-settings&tab=zodiacquality" class="nav-tab <?php echo $active_tab == 'zodiacquality' ? 'nav-tab-active' : ''; ?>">Zodiac Quality</a>
        <a href="?page=date-calc-settings&tab=chinesezodiac" class="nav-tab <?php echo $active_tab == 'chinesezodiac' ? 'nav-tab-active' : ''; ?>">Chinese Zodiac</a>
        <a href="?page=date-calc-settings&tab=planet" class="nav-tab <?php echo $active_tab == 'planet' ? 'nav-tab-active' : ''; ?>">Planet</a>
        <a href="?page=date-calc-settings&tab=generation" class="nav-tab <?php echo $active_tab == 'generation' ? 'nav-tab-active' : ''; ?>">Generation</a>
        <a href="?page=date-calc-settings&tab=decade" class="nav-tab <?php echo $active_tab == 'decade' ? 'nav-tab-active' : ''; ?>">Decade</a>
        <a href="?page=date-calc-settings&tab=date" class="nav-tab <?php echo $active_tab == 'date' ? 'nav-tab-active' : ''; ?>">Month and Day</a>
        <a href="?page=date-calc-settings&tab=flower" class="nav-tab <?php echo $active_tab == 'flower' ? 'nav-tab-active' : ''; ?>">Flower</a>
        <a href="?page=date-calc-settings&tab=stone" class="nav-tab <?php echo $active_tab == 'stone' ? 'nav-tab-active' : ''; ?>">Birthstone</a>
        <a href="?page=date-calc-settings&tab=lifepath" class="nav-tab <?php echo $active_tab == 'lifepath' ? 'nav-tab-active' : ''; ?>">Life Path Number</a>
    </h2>

    <div class="wrap">

        <?php if ($active_tab == 'exportimport') { ?>


            <?php if (isset($_GET['complete'])) { ?>
                <div id="message" class="updated">
                    <p><strong><?php _e('Settings imported.') ?></strong></p>
                </div>
            <?php } ?>

            <form action="admin-post.php" method="post">
                <input type="hidden" name="action" value="date_calc_import">

                <table class="form-table">
                    <tr>
                        <th style="vertical-align:top;">Export</th>
                        <td><textarea class="large-text code" type="textarea" rows="15"><?php echo esc_attr(generate_export_json()); ?></textarea></td>
                    </tr>
                    <tr>
                        <th style="vertical-align:top;">Import</th>
                        <td>
                            <textarea name="import-data" class="large-text code" type="textarea" rows="15"></textarea>
                            <p class="submit"><input type="submit" name="import" id="import" class="button button-primary" value="Import"></p>
                        </td>
                    </tr>
                </table>
            </form>
        <?php } else { ?>

            <form action="options.php" method="post">
                <?php settings_fields('date-calc-settings');

                if ($active_tab == 'general') { ?>
                    <h2>General Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th style="vertical-align:top;">CSVs to load</th>
                            <td><textarea name="date-calc-general-csvs" class="large-text code" type="textarea" rows="30" cols="80"><?php echo esc_attr(get_option('date-calc-general-csvs')); ?></textarea></td>
                        </tr>
                        <tr>
                            <th style="vertical-align:top;">CSVs to ignore</th>
                            <td><textarea name="date-calc-general-ignore" class="regular-text code" type="textarea" rows="10"><?php echo esc_attr(get_option('date-calc-general-ignore')); ?></textarea></td>
                        </tr>
                    </table>

                    <h3>Cache Statistics</h3>
                    <ul>
                        <?php
                        foreach ($all_data as $key => $value) {
                            print '<li>' . $key . ': <b>' . count($value) . '</b> record(s)</li>';
                        }
                        ?>
                    </ul>
                <?php } else { ?>
                    <input type='hidden' name="date-calc-general-csvs" value="<?php echo esc_attr(get_option('date-calc-general-csvs')); ?>" />
                    <input type='hidden' name="date-calc-general-ignore" value="<?php echo esc_attr(get_option('date-calc-general-ignore')); ?>" />
                <?php }

                print_options('Zodiac', 'zodiac', array('capricorn', 'aquarius', 'pisces', 'aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius'), $active_tab);

                print_options('Zodiac Elements', 'zodiacelement', array('fire', 'earth', 'air', 'water'), $active_tab);

                print_options('Zodiac Quality', 'zodiacquality', array('cardinal', 'fixed', 'mutable'), $active_tab);

                print_options('Chinese Zodiac', 'chinesezodiac', array('monkey', 'rooster', 'dog', 'pig', 'rat', 'ox', 'tiger', 'rabbit', 'dragon', 'serpent', 'horse', 'goat'), $active_tab);

                print_options('Planet', 'planet', array('saturn', 'uranus', 'neptune', 'mars', 'venus', 'mercury', 'moon', 'sun', 'pluto', 'jupiter'), $active_tab);

                print_options('Generation', 'generation', array('gi-generation', 'silent-generation', 'baby-boomers', 'generation-x', 'millennials', 'generation-z', 'generation-alpha'), $active_tab, array('G.I. Generation', 'Silent Generation', 'Baby Boomers Generation', 'Generation X', 'Millennials Generation', 'Generation Z', 'Generation Alpha'));

                $decades = array();
                for ($z = 0; $z < (date('Y') - 1900) / 10; $z++) {
                    $decades[] = (1900 + 10 * $z) . 's';
                }
                print_options('Decade', 'decade', $decades, $active_tab);

                print_options('Weekday', 'date', array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), $active_tab);

                print_options('Month', 'date', array('january', 'february', 'march', 'may', 'april', 'june', 'july', 'august', 'september', 'october', 'november', 'december'), $active_tab);

                print_options('Flower', 'flower', array('Carnation', 'Violet', 'Daffodil', 'Sweet Pea/Daisy', 'Lily of the valley', 'Rose', 'Larkspur', 'Gladiolus', 'Aster/Myosotis', 'Marigold', 'Chrysanthemum', 'Narcissus'), $active_tab);

                print_options('Birthstone', 'stone', array('Garnet', 'Amethyst', 'Aquamarine', 'Diamond', 'Emerald', 'Pearl, Moonstone and Alexandrite', 'Ruby', 'Peridot and Sardonyx', 'Sapphire', 'Opal and Tourmaline', 'Topaz and Citrine', 'Tanzanite, Turquoise, Zircon and Topaz'), $active_tab);

                print_options('Life Path Number', 'lifepath', array('1', '2', '3', '4', '5', '6', '7', '8', '9', '11', '22'), $active_tab);

                submit_button();
                ?>
            </form>

        <?php } ?>
    </div>
    <?php
}

function print_options($title, $setting, $options, $active_tab, $option_titles = array())
{
    if (empty($option_titles)) {
        $option_titles = $options;
    }

    if ($active_tab == $setting) { ?>
        <h2><?php echo $title; ?> Description</h2>
        <table class="form-table">
            <?php
            foreach ($options as $k => $z) {
            ?>
                <tr>
                    <th style="vertical-align:top;"><?php echo ucfirst($option_titles[$k]); ?></th>
                    <td><textarea placeholder="" name="date-calc-<?php echo $setting; ?>-<?php echo str_replace(array('/', ' ', ','), '-', strtolower($z)); ?>" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-' . $setting . '-' . str_replace(array('/', ' ', ','), '-', strtolower($z)))); ?></textarea></td>
                </tr>
            <?php } ?>
        </table>

        <?php } else {
        foreach ($options as $z) {
        ?>
            <input type='hidden' name="date-calc-<?php echo $setting; ?>-<?php echo str_replace(array('/', ' ', ','), '-', strtolower($z)); ?>" value="<?php echo esc_attr(get_option('date-calc-' . $setting . '-' . str_replace(array('/', ' ', ','), '-', strtolower($z)))); ?>" />
<?php }
    }
}

// Init
add_shortcode('datecalc', 'datecalc_func');

add_action('admin_menu', function () {
    add_options_page('dateCalc Settings', 'dateCalc Settings', 'manage_options', 'date-calc-settings', 'date_calc_settings_page');
});

add_action('admin_post_date_calc_import', 'date_calc_import');

add_action('admin_init', function () {
    register_setting('date-calc-settings', 'date-calc-general-csvs');
    register_setting('date-calc-settings', 'date-calc-general-ignore');

    $zodiac = array('capricorn', 'aquarius', 'pisces', 'aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn');
    foreach ($zodiac as $z) {
        register_setting('date-calc-settings', 'date-calc-zodiac-' . $z);
    }

    $zodiacElement = array('fire', 'earth', 'air', 'water');
    foreach ($zodiacElement as $z) {
        register_setting('date-calc-settings', 'date-calc-zodiacelement-' . $z);
    }

    $zodiacQuality = array('cardinal', 'fixed', 'mutable');
    foreach ($zodiacQuality as $z) {
        register_setting('date-calc-settings', 'date-calc-zodiacquality-' . $z);
    }

    $chineseZodiac = array('monkey', 'rooster', 'dog', 'pig', 'rat', 'ox', 'tiger', 'rabbit', 'dragon', 'serpent', 'horse', 'goat');
    foreach ($chineseZodiac as $z) {
        register_setting('date-calc-settings', 'date-calc-chinesezodiac-' . $z);
    }

    $planet = array('saturn', 'uranus', 'neptune', 'mars', 'venus', 'mercury', 'moon', 'sun', 'pluto', 'jupiter');
    foreach ($planet as $z) {
        register_setting('date-calc-settings', 'date-calc-planet-' . $z);
    }

    $generation = array('G.I. Generation' => 'gi-generation', 'Silent Generation' => 'silent-generation', 'Baby Boomers' => 'baby-boomers', 'Generation X' => 'generation-x', 'Millennials' => 'millennials', 'Generation Z' => 'generation-z', 'Generation Alpha' => 'generation-alpha');
    foreach ($generation as $z) {
        register_setting('date-calc-settings', 'date-calc-generation-' . $z);
    }

    for ($z = 0; $z < (date('Y') - 1900) / 10; $z++) {
        register_setting('date-calc-settings', 'date-calc-decade-' . (1900 + 10 * $z) . 's');
    }

    $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'january', 'february', 'march', 'may', 'april', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    foreach ($days as $z) {
        register_setting('date-calc-settings', 'date-calc-date-' . $z);
    }

    $flower = array('carnation', 'violet', 'daffodil', 'sweet pea/daisy', 'lily of the valley', 'rose', 'larkspur', 'gladiolus', 'aster/myosotis', 'marigold', 'chrysanthemum', 'narcissus');
    foreach ($flower as $z) {
        register_setting('date-calc-settings', 'date-calc-flower-' . str_replace(array('/', ' ', ','), '-', $z));
    }

    $stone = array('Garnet', 'Amethyst', 'Aquamarine', 'Diamond', 'Emerald', 'Pearl, Moonstone and Alexandrite', 'Ruby', 'Peridot and Sardonyx', 'Sapphire', 'Opal and Tourmaline', 'Topaz and Citrine', 'Tanzanite, Turquoise, Zircon and Topaz');
    foreach ($stone as $z) {
        register_setting('date-calc-settings', 'date-calc-stone-' . str_replace(array('/', ' ', ','), '-', strtolower($z)));
    }

    $lifepath = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '11', '22');
    foreach ($lifepath as $z) {
        register_setting('date-calc-settings', 'date-calc-lifepath-' . str_replace(array('/', ' ', ','), '-', strtolower($z)));
    }
});

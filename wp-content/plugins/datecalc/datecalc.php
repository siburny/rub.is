<?php
/**
 * Plugin Name: Date Calculator and Formatter
 * Description: Flexible date and time formatter
 * Version: 1.8
 */

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
    if ($y % 400 == 0 ||
        $y % 100 != 0 &&
        $y % 4 == 0) {
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
        'I' => 1);

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

function _ordinal_suffix($number)
{
    $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');

    if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
        return 'th';
    } else {
        return $ends[$number % 10];
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

function datecalc_func($atts)
{
    global $billboard;

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

    if (array_key_exists('add', $atts)) {
        if (is_numeric($atts['add'])) {
            if ($atts['add'] > 0) {
                $date->add(new DateInterval('P' . $atts['add'] . 'Y'));
            } else {
                $date->sub(new DateInterval('P' . abs($atts['add']) . 'Y'));
            }
        }
    }

    $display = "yyyy";
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
        $diff = date_diff($date, date_create());
        $ret = $diff->format('%y');
    } else if (array_key_exists('dogyears', $atts) && ($atts['dogyears'] == 'yes' || $atts['dogyears'] == '1' || $atts['dogyears'] == 'true')) {
        $diff = date_diff($date, date_create());
        $ret = floor(($diff->format('%y') - 14) / 4.731136854);
    } else if (array_key_exists('fullage', $atts) && ($atts['fullage'] == 'yes' || $atts['fullage'] == '1' || $atts['fullage'] == 'true')) {
        $diff = date_diff($date, date_create());
        $ret = $diff->format('%y');
        mjohnson\numword\Numword::$sep = ' ';
        $ret = mjohnson\numword\Numword::single($ret);
    } else if (array_key_exists('roman', $atts) && ($atts['roman'] == 'yes' || $atts['roman'] == '1' || $atts['roman'] == 'true')) {
        $ret = ConvertToRoman($date->format('Y'));
    } else if (array_key_exists('zodiac', $atts) && ($atts['zodiac'] == 'yes' || $atts['zodiac'] == '1' || $atts['zodiac'] == 'true')) {
        if (array_key_exists('icon', $atts) && !empty($atts['icon'])) {
            return zodiac($date->format('j'), $date->format('n'), true);
        } else {
            $ret = zodiac($date->format('j'), $date->format('n'));
            return $description ? nl2br_str(get_option('date-calc-zodiac-' . strtolower($ret))) : $ret;
        }
    } else if (array_key_exists('chinesezodiac', $atts) && ($atts['chinesezodiac'] == 'yes' || $atts['chinesezodiac'] == '1' || $atts['chinesezodiac'] == 'true')) {
        $chineseZodiac = array('Monkey', 'Rooster', 'Dog', 'Pig', 'Rat', 'Ox', 'Tiger', 'Rabbit', 'Dragon', 'Serpent', 'Horse', 'Goat');
        $ret = $chineseZodiac[$date->format('Y') % 12];
        return $description ? nl2br_str(get_option('date-calc-chinesezodiac-' . strtolower($ret))) : $ret;
    } else if (array_key_exists('flower', $atts) && ($atts['flower'] == 'yes' || $atts['flower'] == '1' || $atts['flower'] == 'true')) {
        $flower = array('Carnation', 'Violet', 'Daffodil', 'Sweet Pea/Daisy', 'Lily of the valley', 'Rose', 'Larkspur', 'Gladiolus', 'Aster/Myosotis', 'Marigold', 'Chrysanthemum', 'Narcissus');
        $ret = $flower[$date->format('n') - 1];
        return $description ? nl2br_str(get_option('date-calc-flower-' . str_replace(array('/', ' ', ','), '-', strtolower($ret)))) : $ret;
    } else if (array_key_exists('stone', $atts) && ($atts['stone'] == 'yes' || $atts['stone'] == '1' || $atts['stone'] == 'true')) {
        $stone = array('Garnet', 'Amethyst', 'Aquamarine', 'Diamond', 'Emerald', 'Pearl, Moonstone and Alexandrite', 'Ruby', 'Peridot and Sardonyx', 'Sapphire', 'Opal and Tourmaline', 'Topaz and Citrine', 'Tanzanite, Turquoise, Zircon and Topaz');
        $ret = $stone[$date->format('n') - 1];
        return $description ? nl2br_str(get_option('date-calc-stone-' . str_replace(array('/', ' ', ','), '-', strtolower($ret)))) : $ret;
    } else if (array_key_exists('generation', $atts) && ($atts['generation'] == 'yes' || $atts['generation'] == '1' || $atts['generation'] == 'true')) {
        $year = $date->format('Y');
        if ($year <= 1924) {
            $ret = "G.I. Generation";
        } else if ($year <= 1942) {
            $ret = "Silent Generation";
        } else if ($year <= 1964) {
            $ret = "Baby Boomers";
        } else if ($year <= 1979) {
            $ret = "Generation X";
        } else if ($year <= 2000) {
            $ret = "Millennials";
        } else {
            $ret = "Generation Z";
        }
        $generation = array('G.I. Generation' => 'gi-generation', 'Silent Generation' => 'silent-generation', 'Baby Boomers' => 'baby-boomers', 'Generation X' => 'generation-x', 'Millennials' => 'millennials', 'Generation Z' => 'generation-z');
        return $description ? nl2br_str(get_option('date-calc-generation-' . $generation[$ret])) : $ret;
    } else if (array_key_exists('decade', $atts) && ($atts['decade'] == 'yes' || $atts['decade'] == '1' || $atts['decade'] == 'true')) {
        $ret = intval($date->format('Y') / 10) * 10;
        $ret .= 's';
        return $description ? nl2br_str(get_option('date-calc-decade-' . $ret)) : $ret;
    } else if (array_key_exists('samecalendar', $atts) && ($atts['samecalendar'] == 'yes' || $atts['samecalendar'] == '1' || $atts['samecalendar'] == 'true')) {
        $ret = $date->format("Y");
        while (($ret = nextYear($ret)) < date('Y'));
        return $ret;
    } else if (array_key_exists('song', $atts) && ($atts['song'] == 'yes' || $atts['song'] == '1' || $atts['song'] == 'true')) {
        $key = $date->format('n/j/Y');
        if (array_key_exists($key, $billboard)) {
            return '&quot;' . $billboard[$key]['Song'] . '&quot; by ' . $billboard[$key]['Artist'];
        }
        return '';
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
        return $description ? nl2br_str(get_option('date-calc-planet-' . strtolower($ret))) : $ret;
    } else if (array_key_exists('babyname', $atts)) {
        global $babynames;
        $count = array_key_exists('numbers', $atts);

        if (!array_key_exists($date->format('Y'), $babynames)) {
            return '';
        }
        $ret = $babynames[$date->format('Y')];

        if ($count) {
            if ($display == 'girl') {
                return number_format_nozero(str_replace(',', '', $ret['GirlNumber']));
            } else if ($display == 'boy') {
                return number_format_nozero(str_replace(',', '', $ret['BoyNumbers']));
            }
        } else {
            if ($display == 'girl') {
                return str_replace(',', '', $ret['Girl']);
            } else if ($display == 'boy') {
                return str_replace(',', '', $ret['Boy']);
            }
        }
        return '';
    } else if (array_key_exists('nba', $atts)) {
        global $nba;

        if (!array_key_exists($date->format('Y'), $nba)) {
            return '';
        }
        $ret = $nba[$date->format('Y')];

        return $ret['Output'];
    } else if (array_key_exists('nhl', $atts)) {
        global $nhl;

        if (!array_key_exists($date->format('Y'), $nhl)) {
            return '';
        }
        $ret = $nhl[$date->format('Y')];

        return $ret['Output'];
    } else if (array_key_exists('nfl', $atts)) {
        global $nfl;

        if (!array_key_exists($date->format('Y'), $nfl)) {
            return '';
        }
        $ret = $nfl[$date->format('Y')];

        return $ret['Output'];
    } else if (array_key_exists('mlb', $atts)) {
        global $mlb;

        if (!array_key_exists($date->format('Y'), $mlb)) {
            return '';
        }
        $ret = $mlb[$date->format('Y')];

        return $ret['Output'];
    } else if (array_key_exists('birthdays', $atts)) {
        global $birthdays;

        if (!array_key_exists($date->format('n/j/Y'), $birthdays)) {
            return '';
        }
        $ret = $birthdays[$date->format('n/j/Y')];

        $count = 3;
        if (!array_key_exists('count', $atts) && is_numeric($atts['count'])) {
            $count = 0 + $atts['count'];
        }

        if (array_key_exists('order', $atts) && $atts['order'] == 'new') {
            $ret = array_reverse($ret);
        }

        $str = array();
        $i = 0;
        foreach ($ret as $key => $value) {
            if($i++ < $count) {
                $str[] = $value['name'] . ' (' . $value['profession'] . ')';
            }
        }

        return implode(', ', $str);
    } else if (array_key_exists('events', $atts)) {
        global $events;

        if (!array_key_exists($date->format('n/j'), $events)) {
            return '';
        }
        $ret = $events[$date->format('n/j')];

        $count = 3;
        if (!array_key_exists('count', $atts) && is_numeric($atts['count'])) {
            $count = 0 + $atts['count'];
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
    } else if (array_key_exists('babybirth', $atts)) {
        global $babybirths;

        if (!array_key_exists($date->format('Y'), $babybirths)) {
            return '';
        }
        $ret = $babybirths[$date->format('Y')];

        if ($display == 'year') {
            return number_format_nozero(str_replace(',', '', $ret['BabiesYear']));
        } else if ($display == 'month') {
            return number_format_nozero(str_replace(',', '', $ret['BabiesYear']) / 12);
        } else if ($display == 'day') {
            return number_format_nozero(str_replace(',', '', $ret['BabiesDay']));
        } else if ($display == 'minute') {
            return number_format_nozero(str_replace(',', '', $ret['BabiesMinute']));
        }
        return '';
    } else if (array_key_exists('difference', $atts)) {
        $doPlural = function ($nb, $str) {return $nb > 1 ? $str . 's' : $str;};

        $date_diff = new DateTime($atts['difference'], new DateTimeZone(get_option('timezone_string')));
        $diff = $date_diff->diff($date);

        if ($display == 'year') {
            return number_format_nozero($diff->format('%y'));
        } else if ($display == 'month') {
            return number_format_nozero($diff->format('%m') + 12 * $diff->format('%y'));
        } else if ($display == 'week') {
            return number_format_nozero(round($diff->format('%a') / 7));
        } else if ($display == 'day') {
            return number_format_nozero($diff->format('%a'));
        } else if ($display == 'hour') {
            return number_format_nozero($diff->format('%a') * 24 + $diff->h);
        } else if ($display == 'minute') {
            return number_format_nozero(($diff->format('%a') * 24 + $diff->h) * 60 + $diff->i);
        } else if ($display == 'second') {
            return number_format_nozero((($diff->format('%a') * 24 + $diff->h) * 60 + $diff->i) * 60 + $diff->s);
        } else if ($display == 'full') {

            if ($interval->y !== 0) {
                $format[] = "%y " . $doPlural($diff->y, "year");
            }
            if ($interval->m !== 0) {
                $format[] = "%m " . $doPlural($diff->m, "month");
            }
            if ($interval->d !== 0) {
                $format[] = "%d " . $doPlural($diff->d, "day");
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

            return $diff->format($format);
        }

        return '';
    } else if (array_key_exists('sleep', $atts)) {
        $doPlural = function ($nb, $str) {return $nb > 1 ? $str . 's' : $str;};

        $date_diff = new DateTime('now', new DateTimeZone(get_option('timezone_string')));
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

        return $date->format('F j, Y');
    } else if (array_key_exists('moon', $atts)) {
        $date_diff = new DateTime('now', new DateTimeZone(get_option('timezone_string')));
        $diff = $date_diff->diff($date);

        return number_format_nozero(1.0 * $diff->format('%a') / 29.5);
    } else if (array_key_exists('population', $atts)) {
        $population = array('2019' => '7714576923', '2018' => '7632819325', '2017' => '7550262101', '2016' => '7466964280', '2015' => '7383008820', '2014' => '7298453033', '2013' => '7213426452', '2012' => '7128176935', '2011' => '7043008586', '2010' => '6958169159', '2009' => '6873741054', '2008' => '6789771253', '2007' => '6706418593', '2006' => '6623847913', '2005' => '6542159383', '2004' => '6461370865', '2003' => '6381408987', '2002' => '6302149639', '2001' => '6223412158', '2000' => '6145006989', '1999' => '6066867391', '1998' => '5988846103', '1997' => '5910566295', '1996' => '5831565020', '1995' => '5751474416', '1994' => '5670319703', '1993' => '5588094837', '1992' => '5504401149', '1991' => '5418758803', '1990' => '5330943460', '1989' => '5240735117', '1988' => '5148556956', '1987' => '5055636132', '1986' => '4963633228', '1985' => '4873781796', '1984' => '4786483862', '1983' => '4701530843', '1982' => '4618776168', '1981' => '4537845777', '1980' => '4458411534', '1979' => '4380585755', '1978' => '4304377112', '1977' => '4229201257', '1976' => '4154287594', '1975' => '4079087198', '1974' => '4003448151', '1973' => '3927538695', '1972' => '3851545181', '1971' => '3775790900', '1970' => '3700577650', '1969' => '3625905514', '1968' => '3551880700', '1967' => '3479053821', '1966' => '3408121405', '1965' => '3339592688', '1964' => '3273670772', '1963' => '3210271352', '1962' => '3149244245', '1961' => '3090305279', '1960' => '3033212527', '1959' => '2977824686', '1958' => '2924081243', '1957' => '2871952278', '1956' => '2821383444', '1955' => '2772242535', '1954' => '2724302468', '1953' => '2677230358', '1952' => '2630584384', '1951' => '2583816786');

        if (array_key_exists($date->format('Y'), $population)) {
            return number_format_nozero($population[$date->format('Y')]);
        }

        return '';
    } else if (array_key_exists('lifepath', $atts)) {
        $lifepath = function ($n) {
            $n = '' . $n;
            $r = 0;
            for ($i = 0; $i < strlen($n); $i++) {
                $r += $n[$i]-'0';
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
        $f1 = new NumberFormatter("en", NumberFormatter::SPELLOUT);

        $f2 = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        $f2->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

        $ret = $date->format('F') . ' ' . $f2->format($date->format('j')) . ', ' . $f1->format(substr($date->format('Y'), 0, 2)) . ' ' . $f1->format(substr($date->format('Y'), 2, 2));
    } else {
        $count = array_key_exists('count', $atts);

        $display = preg_split("/(yyyy|yy|mmmm|mmm|mm|m|dddd|ddd|dd|d|hh:mm|h:mm|AM\/PM|AMPM|w)/", $display, -1, PREG_SPLIT_DELIM_CAPTURE);
        $replace = array(
            'yyyy' => 'Y',
            'yy' => 'y',
            'mmmm' => 'F',
            'mmm' => 'M',
            'mm' => 'm',
            'm' => 'n',
            'dddd' => $count ? 'W' : 'l',
            'ddd' => 'D',
            'dd' => 'd' . ($ordinalize ? 'S' : ''),
            'd' => $count ? 'z' : 'j' . ($ordinalize ? 'S' : ''),
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
                } else if ($token == 'dddd' && $count) {
                    $ret = $ret . _ordinal_suffix($ret) . ' ' . $date->format('l');
                }
            } else {
                $ret .= $token;
            }
        }

        if ($description) {
            $ret = get_option('date-calc-date-' . $ret);
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

    if ($ordinalize && is_numeric($ret)) {
        $ret .= _ordinal_suffix($ret);
    }

    return $ret;
}

function date_calc_settings_page()
{
    if (isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];
    } else {
        $active_tab = 'zodiac';
    }
    ?>

<h2>Date Calc Plugin Options</h2>

<h2 class="nav-tab-wrapper">
    <a href="?page=date-calc-settings&tab=zodiac" class="nav-tab <?php echo $active_tab == 'zodiac' ? 'nav-tab-active' : ''; ?>">Zodiac</a>
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
    <form action="options.php" method="post">
    <?php settings_fields('date-calc-settings');?>

<?php

    print_options('Zodiac', 'zodiac', array('capricorn', 'aquarius', 'pisces', 'aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius'), $active_tab);

    print_options('Chinese Zodiac', 'chinesezodiac', array('monkey', 'rooster', 'dog', 'pig', 'rat', 'ox', 'tiger', 'rabbit', 'dragon', 'serpent', 'horse', 'goat'), $active_tab);

    print_options('Planet', 'planet', array('saturn', 'uranus', 'neptune', 'mars', 'venus', 'mercury', 'moon', 'sun', 'pluto', 'jupiter'), $active_tab);

    print_options('Generation', 'generation', array('G.I. Generation' => 'gi-generation', 'Silent Generation' => 'silent-generation', 'Baby Boomers' => 'baby-boomers', 'Generation X' => 'generation-x', 'Millennials' => 'millennials', 'Generation Z' => 'generation-z'), $active_tab);

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
   </div>
<?php
}

function print_options($title, $setting, $options, $active_tab)
{
    if ($active_tab == $setting) {?>
        <h2><?php echo $title; ?> Description</h2>
        <table class="form-table">
        <?php
foreach ($options as $z) {
        ?>
                    <tr>
                         <th style="vertial-align:top;"><?php echo $z; ?></th>
                         <td><textarea placeholder="" name="date-calc-<?php echo $setting; ?>-<?php echo str_replace(array('/', ' ', ','), '-', strtolower($z)); ?>" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-' . $setting . '-' . str_replace(array('/', ' ', ','), '-', strtolower($z)))); ?></textarea></td>
                     </tr>
        <?php }?>
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
    add_options_page('Date Calc Settings', 'Date Calc Settings', 'manage_options', 'date-calc-settings', 'date_calc_settings_page');
});

add_action('admin_init', function () {
    $zodiac = array('capricorn', 'aquarius', 'pisces', 'aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn');
    foreach ($zodiac as $z) {
        register_setting('date-calc-settings', 'date-calc-zodiac-' . $z);
    }

    $chineseZodiac = array('monkey', 'rooster', 'dog', 'pig', 'rat', 'ox', 'tiger', 'rabbit', 'dragon', 'serpent', 'horse', 'goat');
    foreach ($chineseZodiac as $z) {
        register_setting('date-calc-settings', 'date-calc-chinesezodiac-' . $z);
    }

    $planet = array('saturn', 'uranus', 'neptune', 'mars', 'venus', 'mercury', 'moon', 'sun', 'pluto', 'jupiter');
    foreach ($planet as $z) {
        register_setting('date-calc-settings', 'date-calc-planet-' . $z);
    }

    $generation = array('G.I. Generation' => 'gi-generation', 'Silent Generation' => 'silent-generation', 'Baby Boomers' => 'baby-boomers', 'Generation X' => 'generation-x', 'Millennials' => 'millennials', 'Generation Z' => 'generation-z');
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

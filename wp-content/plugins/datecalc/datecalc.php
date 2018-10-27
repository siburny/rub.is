<?php
/**
 * Plugin Name: Date Calculator and Formatter
 * Description: Flexible date and time formatter
 * Version: 1.8
 */

require_once 'Numword.php';

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

function datecalc_func($atts)
{
    if (empty($atts)) {
        $atts = array();
    }

    $date = new DateTime($atts['until'], new DateTimeZone(get_option('timezone_string')));
    if (array_key_exists('date', $atts)) {
        if (is_numeric($atts['date'])) {
            return $atts['date'];
        }
        try {
            $date = new DateTime($atts['date'], new DateTimeZone(get_option('timezone_string')));
        } catch (Exception $e) {
            return $atts['date'];
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
    } else if (array_key_exists('fullage', $atts) && ($atts['fullage'] == 'yes' || $atts['fullage'] == '1' || $atts['fullage'] == 'true')) {
        $diff = date_diff($date, date_create());
        $ret = $diff->format('%y');
        mjohnson\numword\Numword::$sep = ' ';
        $ret = mjohnson\numword\Numword::single($ret);
    } else if (array_key_exists('roman', $atts) && ($atts['roman'] == 'yes' || $atts['roman'] == '1' || $atts['roman'] == 'true')) {
        $ret = ConvertToRoman($date->format('Y'));
    } else if (array_key_exists('zodiac', $atts) && ($atts['zodiac'] == 'yes' || $atts['zodiac'] == '1' || $atts['zodiac'] == 'true')) {
        if (!function_exists('zodiac')) {
            function zodiac($day, $month)
            {
                $zodiac = array('', 'Capricorn', 'Aquarius', 'Pisces', 'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn');
                $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);

                return ($day > $last_day[$month]) ? $zodiac[$month + 1] : $zodiac[$month];
            }
        }

        $ret = zodiac($date->format('j'), $date->format('n'));
        return $description ? nl2br(get_option('date-calc-zodiac-' . strtolower($ret))) : $ret;
    } else if (array_key_exists('chinesezodiac', $atts) && ($atts['chinesezodiac'] == 'yes' || $atts['chinesezodiac'] == '1' || $atts['chinesezodiac'] == 'true')) {
        $chineseZodiac = array('Monkey', 'Rooster', 'Dog', 'Pig', 'Rat', 'Ox', 'Tiger', 'Rabbit', 'Dragon', 'Serpent', 'Horse', 'Goat');
        $ret = $chineseZodiac[$date->format('Y') % 12];
        return $description ? nl2br(get_option('date-calc-chinesezodiac-' . strtolower($ret))) : $ret;
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
        return $description ? nl2br(get_option('date-calc-generation-' . $generation[$ret])) : $ret;
    } else if (array_key_exists('decade', $atts) && ($atts['decade'] == 'yes' || $atts['decade'] == '1' || $atts['decade'] == 'true')) {
        $ret = intval($date->format('Y') / 10) * 10;
        $ret .= 's';
        return $description ? nl2br(get_option('date-calc-decade-' . $ret)) : $ret;
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
        return $description ? nl2br(get_option('date-calc-planet-' . strtolower($ret))) : $ret;
    } else {
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
            'dd' => 'd' . ($ordinalize ? 'S' : ''),
            'd' => 'j' . ($ordinalize ? 'S' : ''),
            'h:mm' => 'g:i',
            'hh:mm' => 'h:i',
            'AM/PM' => 'A',
            'AMPM' => 'A',
            'w' => 'z',
        );

        $ret = '';
        foreach ($display as $token) {
            if (array_key_exists($token, $replace)) {
                $ret .= $date->format($replace[$token]);
                if ($token == 'w') {
                    $ret = 1 + intval($ret / 7);
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
    ?>
<div class="wrap">
     <form action="options.php" method="post">
     <h2>Zodiac Description</h2>
       <?php
settings_fields('date-calc-settings');
    ?>
        <table class="form-table">
<?php
$zodiac = array('capricorn', 'aquarius', 'pisces', 'aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius');
    foreach ($zodiac as $z) {
        ?>
            <tr>
                 <th style="vertial-align:top;"><?php echo ucfirst($z); ?></th>
                 <td><textarea placeholder="" name="date-calc-zodiac-<?php echo $z; ?>" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-zodiac-' . $z)); ?></textarea></td>
             </tr>
<?php
}
    ?>
<?php
do_settings_sections('date-calc-chinesezodiac');
    ?>
    </table>

    <h2>Chinese Year Zodiac Description</h2>
    <table class="form-table">
            <?php
$chineseZodiac = array('monkey', 'rooster', 'dog', 'pig', 'rat', 'ox', 'tiger', 'rabbit', 'dragon', 'serpent', 'horse', 'goat');
    foreach ($chineseZodiac as $z) {
        ?>
            <tr>
                 <th style="vertial-align:top;"><?php echo ucfirst($z); ?></th>
                 <td><textarea placeholder="" name="date-calc-chinesezodiac-<?php echo $z; ?>" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-chinesezodiac-' . $z)); ?></textarea></td>
             </tr>
<?php
}
    ?>
    </table>

<h2>Planet Description</h2>
<table class="form-table">
<?php
$planet = array('saturn', 'uranus', 'neptune', 'mars', 'venus', 'mercury', 'moon', 'sun', 'pluto', 'jupiter');
    foreach ($planet as $z) {
        ?>
            <tr>
                 <th style="vertial-align:top;"><?php echo ucfirst($z); ?></th>
                 <td><textarea placeholder="" name="date-calc-planet-<?php echo $z; ?>" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-planet-' . $z)); ?></textarea></td>
             </tr>
<?php
}
    ?>
    </table>

<h2>Generation Description</h2>
<table class="form-table">
<?php
$generation = array('G.I. Generation' => 'gi-generation', 'Silent Generation' => 'silent-generation', 'Baby Boomers' => 'baby-boomers', 'Generation X' => 'generation-x', 'Millennials' => 'millennials', 'Generation Z' => 'generation-z');
    foreach ($generation as $z) {
        ?>
            <tr>
                 <th style="vertial-align:top;"><?php echo ucfirst($z); ?></th>
                 <td><textarea placeholder="" name="date-calc-generation-<?php echo $z; ?>" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-generation-' . $z)); ?></textarea></td>
             </tr>
<?php
}
    ?>
        </table>

<h2>Decades Description</h2>
<table class="form-table">
<?php
for ($z = 0; $z < (date('Y') - 1900) / 10; $z++) {
        ?>
            <tr>
                 <th style="vertial-align:top;"><?php echo (1900 + 10 * $z) . 's'; ?></th>
                 <td><textarea placeholder="" name="date-calc-decade-<?php echo (1900 + 10 * $z); ?>s" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-decade-' . (1900 + 10 * $z) . 's')); ?></textarea></td>
             </tr>
<?php
}
    ?>
        </table>

<h2>Months Description</h2>
<table class="form-table">
<?php
$days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    foreach ($days as $z) {
        ?>
            <tr>
                 <th style="vertial-align:top;"><?php echo ucfirst($z); ?></th>
                 <td><textarea placeholder="" name="date-calc-date-<?php echo $z; ?>" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-date-' . $z)); ?></textarea></td>
             </tr>
<?php
}
    ?>
        </table>

<h2>Months Description</h2>
<table class="form-table">
<?php
$days = array('january', 'february', 'march', 'may', 'april', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    foreach ($days as $z) {
        ?>
            <tr>
                 <th style="vertial-align:top;"><?php echo ucfirst($z); ?></th>
                 <td><textarea placeholder="" name="date-calc-date-<?php echo $z; ?>" rows="5" cols="100"><?php echo esc_attr(get_option('date-calc-date-' . $z)); ?></textarea></td>
             </tr>
<?php
}
    ?>
             <tr>
                <td><?php submit_button();?></td>
            </tr>
        </table>
     </form>
   </div>
<?php
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
});

<?php
/**
* Plugin Name: Date Calculator and Formatter
*/

function _ordinal_suffix($number)
{
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    
	if((($number % 100) >= 11) && (($number%100) <= 13))
	{
        return 'th';
	}
    else
	{
        return $ends[$number % 10];
	}
}

function datecalc_func( $atts )
{
	if(empty($atts))
	{
		$atts = array();
	}
	
	$date = new DateTime($atts['until'], new DateTimeZone(get_option('timezone_string')));
	if(array_key_exists('date', $atts))
	{
		if(is_numeric($atts['date']))
		{
			return $atts['date'];
		}
		try {
			$date = new DateTime($atts['date'], new DateTimeZone(get_option('timezone_string')));
		}
		catch(Exception $e)
		{
			return $atts['date'];
		}
	}
	
	if(array_key_exists('add', $atts))
	{
		if(is_numeric($atts['add']))
		{
			if($atts['add'] > 0)
			{
				$date->add(new DateInterval('P'.$atts['add'].'Y'));
			}
			else
			{
				$date->sub(new DateInterval('P'.abs($atts['add']).'Y'));
			}
		}
	}
	
	$display = "yyyy";
	if(array_key_exists('display', $atts))
	{
		$display = $atts['display'];
	}

	$ordinalize = false;
	if(array_key_exists('ordinalize', $atts) && ($atts['ordinalize'] == 'yes' || $atts['ordinalize'] == '1' || $atts['ordinalize'] == 'true'))
	{
		$ordinalize = true;
	}

	if(array_key_exists('age', $atts) && ($atts['age'] == 'yes' || $atts['age'] == '1' || $atts['age'] == 'true'))
	{
		$diff = date_diff($date, date_create());
		$ret = $diff->format('%y');
	}
	else if(array_key_exists('zodiac', $atts) && ($atts['zodiac'] == 'yes' || $atts['zodiac'] == '1' || $atts['zodiac'] == 'true'))
	{
		if(!function_exists('zodiac'))
		{
			function zodiac($day, $month) {
			  $zodiac = array('', 'Capricorn', 'Aquarius', 'Pisces', 'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn');
			  $last_day = array('', 19, 18, 20, 20, 21, 21, 22, 22, 21, 22, 21, 20, 19);
			  return ($day > $last_day[$month]) ? $zodiac[$month + 1] : $zodiac[$month];
			}
		}

		return zodiac($date->format('n'), $date->format('n'));
	}
	else if(array_key_exists('chinesezodiac', $atts) && ($atts['chinesezodiac'] == 'yes' || $atts['chinesezodiac'] == '1' || $atts['chinesezodiac'] == 'true'))
	{
		$chineseZodiac = array('Monkey','Rooster','Dog','Pig','Rat','Ox','Tiger','Rabbit','Dragon','Serpent','Horse','Goat');
		$ret = $chineseZodiac[$date->format('Y') % 12];
	}
	else
	{
		$display = preg_split("/(yyyy|yy|mmmm|mmm|mm|m|dddd|ddd|dd|d|hh:mm|h:mm|AM\/PM|AMPM)/", $display, -1, PREG_SPLIT_DELIM_CAPTURE); 
		$replace = array(
			'yyyy' => 'Y',
			'yy' => 'y', 'mmmm' => 'F',
			'mmm' => 'M',
			'mm' => 'm',
			'm' => 'n',
			'dddd' => 'l',
			'ddd' => 'D',
			'dd' => 'd'.($ordinalize ? 'S' : ''),
			'd' => 'j'.($ordinalize ? 'S' : ''),
			'h:mm' => 'g:i',
			'hh:mm' => 'h:i',
			'AM/PM' => 'A',
			'AMPM' => 'A',
		);
		
		$ret = '';
		foreach($display as $token)
		{
			if(array_key_exists($token, $replace))
			{
				$ret .= $date->format($replace[$token]);
			}
			else
			{
				$ret .= $token;
			}
		}
	}
	
	if($ordinalize && is_numeric($ret))
	{
		$ret .= _ordinal_suffix($ret);
	}
	
	return $ret;
}

add_shortcode( 'datecalc', 'datecalc_func' );
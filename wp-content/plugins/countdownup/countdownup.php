<?php
/**
* Plugin Name: Count down or Count up
*/

function countdownup_func( $atts )
{
	// defaults
	$delimiter = ' ';
	$large = 0;
	$showzero = 0;
	$expire = 0;

	if(empty($atts))
	{
		$atts = array();
	}
	
	try 
	{
		if(array_key_exists('until', $atts))
		{
			if(is_numeric($atts['until']))
			{
				return '';
			}
			
			$date = new DateTime($atts['until'], new DateTimeZone(get_option('timezone_string')));
			$increment = -1;
		}
		elseif(array_key_exists('since', $atts))
		{
			if(is_numeric($atts['since']))
			{
				return '';
			}

			$date = new DateTime($atts['since'], new DateTimeZone(get_option('timezone_string')));
			$increment = 1;
		}
		else
		{
			return '';
		}
	}
	catch (Exception $e)
	{
		return '';
	}
	
	$loop_time = '';
	$loop_range = '';
	if($date && array_key_exists('loop', $atts))
	{
		if(strtolower(substr($atts['loop'], -1, 1)) == 'd')
		{
			$loop_time = substr($atts['loop'], 0, -1);
			$loop_range = 'd';
		}
		else if(strtolower(substr($atts['loop'], -1, 1)) == 'h')
		{
			$loop_time = substr($atts['loop'], 0, -1);
			$loop_range = 'h';
		}
	}
	
	if(array_key_exists('delimiter', $atts))
	{
		$delimiter = $atts['delimiter'];
	}

	if(array_key_exists('large', $atts) && ($atts['large'] == 'yes' || $atts['large'] == '1' || $atts['large'] == 'true'))
	{
		$large = 1;
	}
	
	if(array_key_exists('showzero', $atts) && ($atts['showzero'] == 'yes' || $atts['showzero'] == '1' || $atts['showzero'] == 'true'))
	{
		$showzero = 1;
	}
	
	if(array_key_exists('expire', $atts) && ($atts['expire'] == 'yes' || $atts['expire'] == '1' || $atts['expire'] == 'true'))
	{
		$expire = 1;
	}
	
	$expireText = ' ';
	if(array_key_exists('expiretext', $atts))
	{
		$expireText = $atts['expiretext'];
	}

	$display = array('year', 'month', 'week', 'day', 'hour', 'minute', 'second');
	if(array_key_exists('display', $atts))
	{
		$display = explode(',', $atts['display']);
	}
		
	$uniqueId = str_replace('.', '_', uniqid("", TRUE));	
	ob_start();
	require("template.php");
	return ob_get_clean();
}

add_shortcode( 'countdownup', 'countdownup_func' );
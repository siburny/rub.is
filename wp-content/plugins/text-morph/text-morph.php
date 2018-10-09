<?php
/**
 * Plugin Name: Text Formater and Morpher
 * Description: Plugin that performs differen text transformations like UPPER/lower case, gender formatting, letter substistutions, etc.
 * Author: Maxim Rubis
 * Author URI: https://rub.is/
 * Version: 0.1.0
 */

function morph_func($atts, $content = '')
{
    if (empty($content)) {
        if (array_key_exists('text', $atts)) {
            $content = $atts['text'];
        } else if (array_key_exists('data', $atts)) {
            $content = $atts['data'];
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

    if (array_key_exists('option', $atts) && is_numeric($atts['option']) && $atts['option'] >= 1 && $atts['option'] <= 4 && (strtolower($ret) == 'male' || strtolower($ret) == 'female')) {
        $ret = strtolower($ret);
        $gender_values = array(
            'male' => array(1 => 'he', 'him', 'his', 'man'),
            'female' => array(1 => 'she', 'her', 'hers', 'woman'),
        );

        $ret = $gender_values[$ret][$atts['option']];
    }

    if (array_key_exists('random', $atts) && !empty($atts['random'])) {
        $values = explode($atts['random'], $ret);
        $ret = $values[mt_rand(0, count($values) - 1)];
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

    return $ret;
}

add_shortcode('morph', 'morph_func');

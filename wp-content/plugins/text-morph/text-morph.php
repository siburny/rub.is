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

    if (array_key_exists('option', $atts) && is_numeric($atts['option']) && $atts['option'] >= 1 && $atts['option'] <= 6 && (strtolower($ret) == 'male' || strtolower($ret) == 'female')) {
        $ret = strtolower($ret);
        $gender_values = array(
            'male' => array(1 => 'he', 'him', 'his', 'man', 'boyfriend', 'husband'),
            'female' => array(1 => 'she', 'her', 'hers', 'woman', 'girlfriend', 'wife'),
        );

        if(array_key_exists('reverse', $atts) && !empty($atts['reverse'])) {
            $ret = $ret == 'male' ? 'female' : 'male';
        }

        $ret = $gender_values[$ret][$atts['option']];
    }

    if (array_key_exists('clean', $atts) && !empty($atts['clean'])) {
        $ret = str_replace(array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+', '=', '\'', '"', ',', '.', '/', '?', '\\', '|', '{', '}', '[', ']', '`', '~', ':', ';'),
            '', $ret);
    }

    if (array_key_exists('random', $atts) && !empty($atts['random'])) {
        $values = explode($atts['random'], $ret);
        $ret = $values[mt_rand(0, count($values) - 1)];
    }

    if (array_key_exists('description', $atts) && !empty($atts['description'])) {
        $options = wp_load_alloptions();
        for ($z = 0; $z < 100; $z++) {
            if (isset($options['text-morph-settings-find-' . $z]) && $options['text-morph-settings-find-' . $z] == $ret) {
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

    return $ret;
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
                <td><?php submit_button();?></td>
            </tr>
            <tr>
                <td><b>Match</b></td>
                <td><b>Replace</b></td>
            </tr>
<?php for ($z = 0; $z < 100; $z++) {?>
            <tr>
                <td><textarea placeholder="" name="text-morph-settings-find-<?php echo $z; ?>" rows="4" cols="30"><?php echo esc_attr(get_option('text-morph-settings-find-' . $z)); ?></textarea></td>
                <td><textarea placeholder="" name="text-morph-settings-replace-<?php echo $z; ?>" rows="4" cols="100"><?php echo esc_attr(get_option('text-morph-settings-replace-' . $z)); ?></textarea></td>
            </tr>
<?php }?>
            <tr>
                <td><?php submit_button();?></td>
            </tr>
        </table>
    </form>
</div>
<?php

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

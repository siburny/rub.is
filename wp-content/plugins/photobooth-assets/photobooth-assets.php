<?php
/**
 * Plugin Name: Photobooth Assets
 * Description: Allows photobooth download templates on-demand
 * Version: 0.1
 */

function my_awesome_func($data)
{
    $attachments = get_children(
        array(
            'numberposts' => -1,
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'title'=> 'photobooth',
        )
    );

    $ret = array();
    array_walk($attachments, function($item, $key) use(&$ret) {
        $ret[] = $item->guid;
    });

    return $ret;
}

// Init
add_action('rest_api_init', function () {
    register_rest_route('photobooth-assets/v1', '/get-templates', array(
        'methods' => 'GET',
        'callback' => 'my_awesome_func',
    ));
});

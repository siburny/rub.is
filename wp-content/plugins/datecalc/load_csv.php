<?php

global $all_data;
$all_data = array();

$csvs = array_filter(explode("\n", get_option('date-calc-general-csvs')));
$csvs = array_map('trim', $csvs);

$ignore = array_filter(explode("\n", get_option('date-calc-general-ignore')));
$ignore = array_map('trim', $ignore);

foreach ($csvs as $url) {
    if (strpos($url, '/') != false) {
        $name = substr($url, strrpos($url, '/') + 1, -4);
    } else {
        $name = $url;
    }
    if (array_search($name, $ignore) !== false) {
        $all_data[$name] = array();
        continue;
    }

    $data = get_transient('date-calc-cache-' . $name);

    if ($data !== false) {
        $all_data[$name] = unserialize(base64_decode(str_replace("\r\n", "", $data)));
    } else {
        $all_data[$name] = array();

        if (file_exists(plugin_dir_path(__FILE__) . 'data/' . $name . '.csv')) {
            $file = @file(plugin_dir_path(__FILE__) . 'data/' . $name . '.csv');
        } else {
            $file = null;
        }

        if (!empty($file)) {
            $file = array_map('trim', $file);
            $input = array_map('str_getcsv', $file);
        } else {
            $file = @file($url . '?t=' . time());
            if (!empty($file)) {
                $file = array_map('trim', $file);
                $input = array_map('str_getcsv', $file);
            } else {
                $input = null;
            }
        }

        if (empty($input)) {
            set_transient('date-calc-cache-' . $name, chunk_split(base64_encode(serialize(array())), 76, "\r\n"), 1 * HOUR_IN_SECONDS);
            set_transient('date-calc-cache-' . $name . '_saved', 'N/A', 1 * HOUR_IN_SECONDS);
            continue;
        }

        array_walk($input[0], function (&$c) {
            $c = strtolower($c);
        });

        $key = 0;
        if (in_array('date', $input[0])) {
            $key = array_search('date', $input[0]);
        } else if (in_array('year', $input[0])) {
            $key = array_search('date', $input[0]);
        }

        $skip = true;

        array_walk($input, function ($a) use (&$all_data, $name, $input, &$skip, $key) {
            if (!$skip) {
                if (!array_key_exists($a[$key], $all_data[$name])) {
                    $all_data[$name][$a[$key]] = array();
                }

                if (count($input[0]) != count($a)) {
                    print '<!-- Dynamic CVS error: ' . $input . ' -->';
                } else {
                    $all_data[$name][$a[$key]][] = array_combine($input[0], $a);
                }
            }
            $skip = false;
        });

        $expiration = random_int(12, 25) * HOUR_IN_SECONDS;
        $date = new DateTime('now', new DateTimeZone(get_option('timezone_string')));
        set_transient('date-calc-cache-' . $name, chunk_split(base64_encode(serialize($all_data[$name])), 76, "\r\n"), $expiration);
        set_transient('date-calc-cache-' . $name . '_saved', $date->format(DATE_RFC2822), $expiration);
    }
}

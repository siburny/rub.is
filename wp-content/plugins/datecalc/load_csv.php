<?php

global $all_data;
$all_data = array();

$csvs = array_filter(explode("\n", get_option('date-calc-general-csvs')));
$csvs = array_map('trim', $csvs);

$ignore = array_filter(explode("\n", get_option('date-calc-general-ignore')));
$ignore = array_map('trim', $ignore);

foreach ($csvs as $url) {
    $name = substr($url, strrpos($url, '/') + 1, -4);
    if(array_search($name, $ignore) !== false) {
        $all_data[$name] = array();
        continue;
    }

    $data = get_transient('date-calc-cache-' . $name);

    if ($data !== false) {
        $all_data[$name] = unserialize(base64_decode(str_replace( "\r\n", "", $data)));
    } else {
        $all_data[$name] = array();

        if (file_exists(plugin_dir_path(__FILE__) . 'data/' . $name . '.csv')) {
            $file = @file(plugin_dir_path(__FILE__) . 'data/' . $name . '.csv');
        } else {
            $file = null;
        }

        if (!empty($file)) {
            $input = array_map('str_getcsv', $file);
        } else {
            $file = @file($url);
            if (!empty($file)) {
                $input = array_map('str_getcsv', $file);
            } else {
                $input = null;
            }
        }

        if (empty($input)) {
            set_transient('date-calc-cache-' . $name, chunk_split(base64_encode(serialize(array())), 76, "\r\n"), 1 * HOUR_IN_SECONDS);
            continue;
        }

        array_walk($input[0], function (&$c) {
            $c = strtolower($c);
        });

        $skip = true;
        array_walk($input, function ($a) use (&$all_data, $name, $input, &$skip) {
            if (!$skip) {
                if (!array_key_exists($a[0], $all_data[$name])) {
                    $all_data[$name][$a[0]] = array();
                }
                $all_data[$name][$a[0]][] = array_combine($input[0], $a);
            }
            $skip = false;
        });

        set_transient('date-calc-cache-' . $name, chunk_split(base64_encode(serialize($all_data[$name])), 76, "\r\n"), random_int(12, 25) * HOUR_IN_SECONDS);
    }
}

/*
$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/events.csv'));
array_walk($input, function ($a) use (&$events, $input) {
    if (is_numeric(substr($a[0], strrpos($a[0], '/') + 1))) {
        $key = substr($a[0], 0, strrpos($a[0], '/'));
        if (!array_key_exists($key, $events)) {
            $events[$key] = array();
        }
        $events[$key][0 + substr($a[0], strrpos($a[0], '/') + 1)] = array_combine($input[0], $a);
    }
});


$skip = true;
$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/presidents.csv'));
array_walk($input, function ($a) use (&$presidents, $input) {
    global $skip;
    if (!$skip) {
        $presidents[$a[0]] = array_combine($input[0], $a);
        $presidents[$a[0]]['Took office'] = date_create($presidents[$a[0]]['Took office'], new DateTimeZone(get_option('timezone_string')));
        if ($presidents[$a[0]]['Left office'] != 'Incumbent') {
            $presidents[$a[0]]['Left office'] = date_create($presidents[$a[0]]['Left office'], new DateTimeZone(get_option('timezone_string')));
        }
    } else {
        $skip = false;
    }
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/birthdays.csv'));
array_walk($input, function ($a) use (&$birthdays, $input) {
    if (!array_key_exists($a[0], $birthdays)) {
        $birthdays[$a[0]] = array();
    }

    $birthdays[$a[0]][] = array_combine($input[0], $a);
});

/*
$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/billboard-top100.csv'));
array_walk($input, function ($a) use (&$billboard, $input) {
    $billboard[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/babynames.csv'));
array_walk($input, function ($a) use (&$babynames, $input) {
    $babynames[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/babybirths.csv'));
array_walk($input, function ($a) use (&$babybirths, $input) {
    $babybirths[$a[0]] = array_combine($input[0], $a);
});

$skip = true;
$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/movies.csv'));
array_walk($input, function ($a) use (&$movies, $input) {
    global $skip;
    if (!$skip) {
        $movies[$a[0]] = array_combine($input[0], $a);
    } else {
        $skip = false;
    }
});

$skip = true;
$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/games.csv'));
array_walk($input, function ($a) use (&$games, $input) {
    global $skip;
    if (!$skip) {
        $games[$a[0]] = array_combine($input[0], $a);
    } else {
        $skip = false;
    }
});

$skip = true;
$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/holidays.csv'));
array_walk($input, function ($a) use (&$holidays, $input, &$skip) {
    if (!$skip) {
        $holidays[] = array_combine($input[0], $a);
    } else {
        $skip = false;
    }
});

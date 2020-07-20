<?php

global
    $billboard,
    $babynames,
    $babybirths,
    $nhl,
    $nfl,
    $mlb,
    $nba,
    $events,
    $birthdays,
    $presidents,
    $movies,
    $games,
    $worldcup,
    $australianopen,
    $cricket,
    $frenchopen,
    $marchmadness,
    $usopen,
    $wimbledon,
    $markets,
    $holidays,
    $all_data;

$billboard = array();
$babynames = array();
$babybirths = array();
$nhl = array();
$nfl = array();
$mlb = array();
$nba = array();
$events = array();
$birthdays = array();
$presidents = array();
$movies = array();
$games = array();
$worldcup = array();
$australianopen = array();
$cricket = array();
$frenchopen = array();
$marchmadness = array();
$usopen = array();
$wimbledon = array();
$markets = array();
$holidays = array();

$all_data = array();

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

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/birthdays.csv'));
array_walk($input, function ($a) use (&$birthdays, $input) {
    if (!array_key_exists($a[0], $birthdays)) {
        $birthdays[$a[0]] = array();
    }

    $birthdays[$a[0]][] = array_combine($input[0], $a);
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

// LOAD DYNAMIC CSVs
$csvs = scandir(plugin_dir_path(__FILE__) . 'data/');
$csvs = array_values(array_filter($csvs, function ($el) {
    return substr($el, -4) == '.csv';
}));

foreach ($csvs as $file) {
    $name = substr($file, 0, -4);
    $all_data[$name] = array();

    // read
    $input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'data/' . $file));
    // process columns
    array_walk($input[0], function (&$c) {
        $c = strtolower($c);
    });
    // load
    array_walk($input, function ($a) use (&$all_data, $name, $input) {
        $all_data[$name][$a[0]] = array_combine($input[0], $a);
    });
}

$skip = true;
$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'csv/holidays.csv'));
array_walk($input, function ($a) use (&$holidays, $input, &$skip) {
    if (!$skip) {
        $holidays[] = array_combine($input[0], $a);
    } else {
        $skip = false;
    }
});

<?php

$billboard = array();
$babynames = array();
$babybirths = array();
$nhl = array();
$nfl = array();
$mlb = array();
$nba = array();
$events = array();
$birthdays = array();

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'billboard-top100.csv'));
array_walk($input, function ($a) use (&$billboard, $input) {
    $billboard[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'babynames.csv'));
array_walk($input, function ($a) use (&$babynames, $input) {
    $babynames[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'babybirths.csv'));
array_walk($input, function ($a) use (&$babybirths, $input) {
    $babybirths[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'nhl.csv'));
array_walk($input, function ($a) use (&$nhl, $input) {
    $nhl[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'nfl.csv'));
array_walk($input, function ($a) use (&$nfl, $input) {
    $nfl[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'mlb.csv'));
array_walk($input, function ($a) use (&$mlb, $input) {
    $mlb[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'nba.csv'));
array_walk($input, function ($a) use (&$nba, $input) {
    $nba[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'events.csv'));
array_walk($input, function ($a) use (&$events, $input) {
    if (is_numeric(substr($a[0], strrpos($a[0], '/') + 1))) {
        $key = substr($a[0], 0, strrpos($a[0], '/'));
        if (!array_key_exists($key, $events)) {
            $events[$key] = array();
        }
        $events[$key][0 + substr($a[0], strrpos($a[0], '/') + 1)] = array_combine($input[0], $a);
    }
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'birthdays.csv'));
array_walk($input, function ($a) use (&$birthdays, $input) {
    if (!array_key_exists($a[0], $birthdays)) {
        $birthdays[$a[0]] = array();
    }

    $birthdays[$a[0]][] = array_combine($input[0], $a);
});

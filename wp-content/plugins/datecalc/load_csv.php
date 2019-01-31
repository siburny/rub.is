<?php

$billboard = array();
$babynames = array();
$babybirths = array();
$nhl = array();
$nfl = array();
$mlb = array();
$nba = array();

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'billboard-top100.csv'));
array_walk($input, function ($a) use(&$billboard, $input) {
   $billboard[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'babynames.csv'));
array_walk($input, function ($a) use(&$babynames, $input) {
   $babynames[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'babybirths.csv'));
array_walk($input, function ($a) use(&$babybirths, $input) {
   $babybirths[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'nhl.csv'));
array_walk($input, function ($a) use(&$nhl, $input) {
   $nhl[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'nfl.csv'));
array_walk($input, function ($a) use(&$nfl, $input) {
   $nfl[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'mlb.csv'));
array_walk($input, function ($a) use(&$mlb, $input) {
   $mlb[$a[0]] = array_combine($input[0], $a);
});

$input = array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'nba.csv'));
array_walk($input, function ($a) use(&$nba, $input) {
   $nba[$a[0]] = array_combine($input[0], $a);
});

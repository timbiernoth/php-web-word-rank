<?php

////////////////////////////////////////////////////////////////////////////////

error_reporting(E_ALL);
ini_set('display_errors', 1);

set_time_limit(600);

define('DEBUG', true);

define('ALLOW_RANKTYPE', true);
define('ALLOW_WORDTYPE', true);

define('ALLOW_WORDTYPE_WORD', true);
define('ALLOW_WORDTYPE_PHRASE', false);
define('ALLOW_WORDTYPE_COMBINE', false);
define('SHOW_WORDTYPE_SELECT', true);

define('OVERWRITE_RANKTYPE', '');
define('OVERWRITE_WORDTYPE', '');

////////////////////////////////////////////////////////////////////////////////

$url = [];
$load_time = [];

require_once 'php/helper.php';

/******************/
if (DEBUG !== false) {$load_time = new LoadTime;}
/******************/

////////////////////////////////////////////////////////////////////////////////

$get_url = new GetUrl($url);

define('RANKTYPE', $get_url->rankType);
define('WORDTYPE', $get_url->wordType);

////////////////////////////////////////////////////////////////////////////////

if ($get_url->error !== true) {

    $to_require = [
        'classes/Helper.class.php',
        'classes/HTML.class.php',
        'classes/WordRank.class.php',
    ];

    foreach ($to_require as $require) {
        require_once 'php/' .$require;
    }
}

////////////////////////////////////////////////////////////////////////////////

if ($get_url->error !== true) {
    $wr = new WordRank([
        'urls' => $get_url->urls,
        'query' => $get_url->query,
        'compare' => $get_url->compare,
        'output' => '',
    ]);
}

////////////////////////////////////////////////////////////////////////////////

require_once 'partials/wrapper.php';

////////////////////////////////////////////////////////////////////////////////

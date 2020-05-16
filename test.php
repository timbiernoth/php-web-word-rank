<?php

////////////////////////////////////////////////////////////////////////////////

error_reporting(E_ALL);
ini_set('display_errors', 1);

////////////////////////////////////////////////////////////////////////////////

define('ALLOW_RANKTYPE', false);
define('ALLOW_WORDTYPE', false);

define('ALLOW_WORDTYPE_WORD', false);
define('ALLOW_WORDTYPE_PHRASE', false);
define('ALLOW_WORDTYPE_COMBINE', false);

////////////////////////////////////////////////////////////////////////////////

$url = [];
$html = [];

require_once 'php/helper.php';
$get_url = new GetUrl($url);

if ($get_url->error !== true) {

    $to_require = [
        'classes/Helper.class.php',
        'classes/HTML.class.php',
    ];

    foreach ($to_require as $require) {
        require_once 'php/' .$require;
    }

    $html = new HTML($get_url->urls);
}

////////////////////////////////////////////////////////////////////////////////

if ( ! empty($get_url->errorMassage) ) {
    echo $get_url->errorMassage;
    exit;
}

// $html = html_entity_decode($html, ENT_QUOTES | ENT_XML1, 'UTF-8')

$page_0 = clean(html_entity_decode($html->htmlsBody[$html->urls[0]]));
$page_1 = clean(html_entity_decode($html->htmlsBody[$html->urls[1]]));

$lengths = array_map('strlen', $html->htmlsBody);

////

$header = '';

$i = 0;
while ($i <= max($lengths)) {

    if ($page_0[$i] == $page_1[$i]) {
        $header .= $page_0[$i];
        $i++;
    } else {
        break;
    }

}

#prexit($header);

//////

function utf8_strrev($str){
    preg_match_all('/./us', $str, $ar);
    return join('', array_reverse($ar[0]));
}

$pages_reverse_0 = utf8_strrev($page_0);
$pages_reverse_1 = utf8_strrev($page_1);

$footer_rev = '';

$i = 0;
while ($i <= max($lengths)) {

    if ($pages_reverse_0[$i] == $pages_reverse_1[$i]) {
        $footer_rev .= $pages_reverse_0[$i];
        $i++;
    } else {
        break;
    }

}

$footer = substr(utf8_strrev($footer_rev), 1);

#prexit($footer);

/////

$page_0_unique = str_replace([$header, $footer], '', $page_0);
$first_tag_temp_1 = @explode('</', $page_0_unique);
$first_tag_temp_2 = @explode('>', $first_tag_temp_1[1]);
$first_tag = '<' . $first_tag_temp_2[0] . '> ';
$page_0_unique = $first_tag . $page_0_unique;

$page_1_unique = str_replace([$header, $footer], '', $page_1);
$first_tag_temp_1 = @explode('</', $page_1_unique);
$first_tag_temp_2 = @explode('>', $first_tag_temp_1[1]);
$first_tag = '<' . $first_tag_temp_2[0] . '> ';
$page_1_unique = $first_tag . $page_1_unique;

echo $page_1_unique;


///////////

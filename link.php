<?php

////////////////////////////////////////////////////////////////////////////////

error_reporting(E_ALL);
ini_set('display_errors', 1);

////////////////////////////////////////////////////////////////////////////////

define('ALLOW_RANKTYPE', true);
define('ALLOW_WORDTYPE', true);

define('ALLOW_WORDTYPE_WORD', true);
define('ALLOW_WORDTYPE_PHRASE', false);
define('ALLOW_WORDTYPE_COMBINE', false);

define('OVERWRITE_RANKTYPE', 'all');
define('OVERWRITE_WORDTYPE', 'word');

////////////////////////////////////////////////////////////////////////////////

$url = [];
$html_obj = [];

require_once 'php/helper.php';
$get_url = new GetUrl($url);

define('RANKTYPE', $get_url->rankType);
define('WORDTYPE', $get_url->wordType);

if ($get_url->error !== true) {

    $to_require = [
        'classes/Helper.class.php',
        'classes/HTML.class.php',
        'classes/WordRank.class.php',
    ];

    foreach ($to_require as $require) {
        require_once 'php/' .$require;
    }

    $html_obj = new HTML($get_url->urls);
}

////////////////////////////////////////////////////////////////////////////////

if ( ! empty($get_url->errorMassage) ) {
    echo $get_url->errorMassage;
    exit;
}

////////////////////////////////////////////////////////////////////////////////

$url = $get_url->urls[0];
$html = $html_obj->htmlsBody[$url];

$html = preg_replace("#\s(id|class)='[^']+'#", '', $html);
$html = preg_replace("#\s(id|class)=\"[^\"]+\"#", '', $html);

$doc = get_doc($html);

/////////

$new_html = '';

$i = 0;
foreach (@explode('<a ', $html) as $val) {
    $prefix = '';
    if ($i !== 0) {
        $prefix = '<a id="' . $i . '" ';
    }
    $new_html .= $prefix . $val;
    $i++;
}

//////////////////

$example = [

    'links' => [

        0 => [

            #'id' => 0, // der wievielte a-tag im body von oben nach unten ist das
            #'tag' => 'a', // a || link
            #'type' => 'intern', // intern || extern

            #'parent' => [
            #    'block' => '', // der unmittelbare - p, h1-h6, div, li, th, td, dd, dt
            #    'inline' => '', // der unmittelbare - strong, b, em, i, u
            #],

            #'content' => [
            #    'anchor' => '', // alt-attr + text img - erste 7 wörter ohne stoppwörter
            #    'anchor_full' => '', // alt-attr img + text - komplett
            #    'anchor_full_origin' => '', // full-html
            #    'before' => '', // text vorher
            #    'after' => '', // text danach
            #    'before_origin' => '', // html vorher
            #    'after_origin' => '', // html danach
            #    position' => '', // % anteil der wörter wo der link ist
            #],

            'attribute' => [
                'href' => 'https://www.abc.de/xyz/',
            #    'href_origin' => 'https://abc.de/xyz/#abc',
                'href_hash' => 'abc', // erster hash-tag bei mehreren
                'href_hash_origin' => '#abc',
            #    'title_origin' => '',
            #    'hreflang' => '',
            #    'lang' => '',
            #    'rel' => '',
            ],

        ],

    ],

    'page' => [

        'status' => 200,
        'url' => '', // auf welcher ist dieser link gerade
        'canonical' => '',
        'is_canonical' => true,
        'is_blocked' => false, // by robots.txt
        'is_nofollow' => false, // by meta robots nofollow und/oder noindex
        'type' => 'www-page', // www-home, www-page, sub-home, sub-page
        'country' => '',
        'language' => '',
        'top_30_keywords' => [], // by wordrank
        'a_count' => [
            'intern' => '',
            'extern' => '',
            'intern_nofollow' => '',
            'extern_nofollow' => '',
        ],

    ],
];

$links = [];

//////////////////

$temp_html = str_replace('</a>', '[/a]', $new_html);
$temp_html = preg_replace( "/<a id=\"(.*?)\"(.*?)>/is", "[a id=\"$1\"]", $temp_html);
$temp_html = clean(strip_tags($temp_html));

$words = [];
foreach (@explode(' ', $temp_html) as $key => $word) {
    array_push($words, $word);
}

$positions = [
    10 => [],
    20 => [],
    30 => [],
    40 => [],
    50 => [],
    60 => [],
    70 => [],
    80 => [],
    90 => [],
    100 => [],
];

$count = count($words);
for ($i = 0; $i < $count; $i++) {

    if ($i <= ($count/10) ) {
        array_push($positions[10], $words[$i]);
    } else if ($i > ($count/10) && $i <= (($count/10)*2) ) {
        array_push($positions[20], $words[$i]);
    } else if ($i > (($count/10)*2) && $i <= (($count/10)*3) ) {
        array_push($positions[30], $words[$i]);
    } else if ($i > (($count/10)*3) && $i <= (($count/10)*4) ) {
        array_push($positions[40], $words[$i]);
    } else if ($i > (($count/10)*4) && $i <= (($count/10)*5) ) {
        array_push($positions[50], $words[$i]);
    } else if ($i > (($count/10)*5) && $i <= (($count/10)*6) ) {
        array_push($positions[60], $words[$i]);
    } else if ($i > (($count/10)*6) && $i <= (($count/10)*7) ) {
        array_push($positions[70], $words[$i]);
    } else if ($i > (($count/10)*7) && $i <= (($count/10)*8) ) {
        array_push($positions[80], $words[$i]);
    } else if ($i > (($count/10)*8) && $i <= (($count/10)*9) ) {
        array_push($positions[90], $words[$i]);
    } else {
        array_push($positions[100], $words[$i]);
    }

}

$position_text = [];

foreach ($positions as $percentage => $words) {
    $word_str = '';
    foreach ($words as $key => $word) {
        $word_str .= $word . ' ';
    }
    $position_text[$percentage] = $word_str;
}

//////////////////

$count = 0;
$doc = get_doc($new_html);
foreach ($doc->getELementsByTagName('a') as $a) {

    $id = $a->getAttribute('id');

    $links[$url]['links'][$id]['id'] = $id;
    $links[$url]['links'][$id]['tag'] = $a->tagName;
    $links[$url]['links'][$id]['parent'] = get_parents($a);

    $anchor_org = remove_stopwords_string($html_obj->helper->clean($a->nodeValue));
    $anchor = '';
    $i = 0;
    foreach (@explode(' ', $anchor_org) as $word) {
        if ($i <= 6) {
            $anchor .= $word . ' ';
        } else {
            break;
        }
        $i++;
    }

    $links[$url]['links'][$id]['content']['anchor'] = clean($anchor);
    $links[$url]['links'][$id]['content']['anchor_full'] = clean($a->nodeValue);
    $links[$url]['links'][$id]['content']['anchor_full_origin'] = clean(get_node_html($a));

    $links[$url]['links'][$id]['attribute']['href_origin'] = clean($a->getAttribute('href'));
    $links[$url]['links'][$id]['attribute']['hreflang'] = $a->getAttribute('hreflang');
    $links[$url]['links'][$id]['attribute']['lang'] = $a->getAttribute('lang');
    $links[$url]['links'][$id]['attribute']['rel'] = $a->getAttribute('rel');

    $links[$url]['links'][$id]['content']['position'] = get_array_key('id="'.$id.'"', $position_text);

    $count++;
}

//////////////////

$temp = [];

$count = 0;
foreach (@explode('<a ', $new_html) as $a_val_1) {

    $id = 0;
    if ($count >= 1) {
        preg_match_all("/id=\"(.*?)\"/is", $a_val_1, $matches);
        $id =  @$matches[1][0];
    }

    $temp[$url][$id]['before'] = $a_val_1;

    foreach (@explode('</a>', $a_val_1) as $a_val_2) {
        $temp[$url][$id]['after'] = $a_val_2;
    }

    $count++;
}

foreach ($temp[$url] as $id => $content) {
    if ($id >= 1) {
        $links[$url]['links'][$id]['content']['prev'] = clean(strip_tags($temp[$url][($id-1)]['after']));
        $links[$url]['links'][$id]['content']['prev_origin'] = clean($temp[$url][($id-1)]['after']);
        $links[$url]['links'][$id]['content']['next'] = clean(strip_tags($temp[$url][$id]['after']));
        $links[$url]['links'][$id]['content']['next_origin'] = clean($temp[$url][$id]['after']);
    }
}

//////////

if ($get_url->error !== true) {
    $wr = new WordRank([
        'urls' => $get_url->urls,
        'query' => $get_url->query,
        'compare' => $get_url->compare,
        'output' => '',
    ]);
}

//////////

$api = $wr->api;

$count_keywords = $api['wordrank']['rank'][$url]['statistics']['keywords_count'];

$keywords = [];
foreach ($api['wordrank']['rank'][$url]['keywords'] as $keyword => $value) {
    $keywords[$keyword] = $value['wordrank'];
}

foreach ($links[$url]['links'] as $id => $data) {
    $score = 0;
    $anchor = clean(str_replace([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], '', $data['content']['anchor']));
    foreach (@explode(' ', $anchor) as $word) {
        if ($word !== '') {
            $score += $keywords[$word];
        }
    }
    $factor = 1;
    if ( substr_count($anchor, ' ') >= 1 ) {
        $factor = substr_count($anchor, ' ') + 1;
    }
    $wordrank_score = round($score / $factor, 2);

    $links[$url]['links'][$id]['rank']['details']['anchor'] = $wordrank_score;

    ////

    $position = $links[$url]['links'][$id]['content']['position'];
    if ($position >= 80) {
        $position = 90;
    }

    $position_score = 100 - $position;

    $links[$url]['links'][$id]['rank']['details']['position'] = $position_score;

    ////

    $component = $links[$url]['links'][$id]['parent']['block'];
    $component_score = 5;
     if (
        $component == 'p' ||
        $component == 'blockquote' ||
        $component == 'q' ||
        $component == 'figure' ||
        $component == 'figcaption'
        ) {
        $component_score = 100;
    } else if (
        $component == 'body' ||
        $component == 'div' ||
        $component == 'td' ||
        $component == 'dd' ||
        $component == 'main'
        ) {
        $component_score = 50;
    } else if (
        $component == 'h1' ||
        $component == 'h2' ||
        $component == 'h3' ||
        $component == 'h4'
        ) {
        $component_score = 30;
    } else if (
        $component == 'th' ||
        $component == 'dt' ||
        $component == 'h5' ||
        $component == 'h6'
        ) {
        $component_score = 15;
    }

    $links[$url]['links'][$id]['rank']['details']['component'] = $component_score;

    ////

    $prev_words = substr_count($links[$url]['links'][$id]['content']['prev'], ' ') + 1;
    $next_words = substr_count($links[$url]['links'][$id]['content']['next'], ' ') + 1;
    $words_avg = ($prev_words + $next_words) / 2;

    $distance_score = 5;
    if ($words_avg >= 20) {
        $distance_score = 100;
    } else if ($words_avg >= 10) {
        $distance_score = 50;
    } else if ($words_avg >= 5) {
        $distance_score = 30;
    } else if ($words_avg > 2) {
        $distance_score = 15;
    }

    $links[$url]['links'][$id]['rank']['details']['distance'] = $distance_score;

    ////

    $links[$url]['links'][$id]['rank']['score'] =
        ($wordrank_score +
        $position_score +
        $component_score +
        $distance_score) / 4
    ;

}

$link_ranks = [];
$link_count = count($links[$url]['links']);

foreach ($links[$url]['links'] as $id => $data) {
    $link_ranks[$id] = [
        'anchor' => $links[$url]['links'][$id]['content']['anchor'],
        'href_origin' => $links[$url]['links'][$id]['attribute']['href_origin'],
        'score' => round($links[$url]['links'][$id]['rank']['score'] / $link_count, 2),
        'score_origin' => $links[$url]['links'][$id]['rank']['score'],
    ];
}

usort($link_ranks, function($a, $b) {
    return $b['score'] - $a['score'];
});

prexit($link_ranks);

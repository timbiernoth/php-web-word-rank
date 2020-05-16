<?php

////////////////////////////////////////////////////////////////////////////////

if ( ! function_exists('pre') ) {
    function pre($input)
    {
        if ( isset($input) ) {
            echo '<pre>' . "\n";
            print_r($input);
            echo "\n" . '</pre>';
        } else {
            return false;
        }
    }
}

if ( ! function_exists('prexit') ) {
    function prexit($input)
    {
        pre($input);
        exit;
    }
}

////////////////////////////////////////////////////////////////////////////////

if ( ! function_exists('get_microtime') ) {
    function get_microtime()
    {
        $time = microtime();
        $time = @explode(' ', $time);
        $time = $time[1] + $time[0];
        $t = $time;
        unset($time);
        return $t;
    }
}

if ( ! function_exists('remove_html_attributes') ) {
    function remove_html_attributes($html)
    {
        return preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $html);
    }
}

if ( ! function_exists('remove_empty_html_tags') ) {
    function remove_empty_html_tags($str, $repto = NULL)
    {
        if ( ! is_string ($str) || trim ($str) == '') {
            return $str;
        }

        return preg_replace (
            '/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU',
            !is_string ($repto) ? '' : $repto,
            $str
        );
    }
}

if ( ! function_exists('clean') ) {
    function clean($input)
    {
        $input = str_replace(array("\t", "\n", "
        "), ' ', $input);

        for ($i = 0; $i < substr_count($input, '  '); $i++) {
            $input = str_replace('  ', ' ', $input);
        }
        for ($i = 0; $i < substr_count($input, '  '); $i++) {
            $input = str_replace('  ', ' ', $input);
        }
        for ($i = 0; $i < substr_count($input, '  '); $i++) {
            $input = str_replace('  ', ' ', $input);
        }

        $input = str_replace(' >', '>', $input);
        $input = str_replace('< ', '<', $input);
        $input = str_replace('> <', '><', $input);
        $input = str_replace('>', '> ', $input);
        $input = str_replace('<', ' <', $input);

        for ($i = 0; $i < substr_count($input, '  '); $i++) {
            $input = str_replace('  ', ' ', $input);
        }

        return mb_strtolower(trim($input));
    }
}

if ( ! function_exists('count_words') ) {
    function count_words($haystack, $needle)
    {
        $count = 0;

        if (substr_count($needle, ' ') >= 1) {
            $count = substr_count(clean($haystack), clean($needle));
        } else {

            $words = [];
            if (is_array($haystack)) {
                $words = $haystack;
            } else {
                foreach (@explode(' ', $haystack) as $word) {
                    array_push($words, $word);
                }
            }

            $count = 0;
            foreach ($words as $word) {
                if (strtolower($word) === strtolower($needle)) {
                    $count++;
                }
            }
        }

        return $count;
    }
}

if ( ! function_exists('zero') ) {
    function zero($arr, $str)
    {
        if ( isset($arr[$str]) ) {
            return $arr[$str];
        }
        return 0;
    }
}

if ( ! function_exists('get_stopwords') ) {
    function get_stopwords($lang = 'de')
    {
        include('stopwords.php');

        $stopwords = [];
        foreach (@explode("\n", $stopwords_string[$lang]) as $stopword) {
            array_push($stopwords, mb_strtolower($stopword));
        }

        array_multisort(array_map('strlen', $stopwords), $stopwords);
        $stopwords = array_reverse($stopwords);

        return $stopwords;
    }
}

if ( ! function_exists('remove_stopwords_string') ) {
    function remove_stopwords_string($str, $lang = 'de')
    {
        $stopwords = get_stopwords($lang);

        $output = '';
        foreach (@explode(' ', $str) as $word) {
            if ( ! in_array(mb_strtolower($word), $stopwords) ) {
                $output .= ' ' . $word . ' ';
            }
        }

        return clean($output);
    }
}

if ( ! function_exists('remove_stopwords') ) {
    function remove_stopwords($arr, $lang = 'de')
    {
        $stopwords = get_stopwords($lang);

        $output = [];
        foreach ($arr as $phrase => $value) {
            if ( ! in_array(mb_strtolower($phrase), $stopwords) ) {
                $output[$phrase] = $value;
            }
        }

        return $output;
    }
}

if ( ! function_exists('remove_withnumbers') ) {
    function remove_withnumbers($arr)
    {
        $numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];

        $output = [];
        foreach ($arr as $phrase => $value) {

            $number_exists = false;
            foreach ($numbers as $number) {
                if ( substr_count($phrase, $number) >= 1 ) {
                    $number_exists = true;
                }
            }
            if ($number_exists !== true) {
                $output[$phrase] = $value;
            }
        }

        return $output;
    }
}

if ( ! function_exists('remove') ) {
    function remove($params, $arr)
    {
        if ($params['stopwords'] !== false) {
            $arr = remove_stopwords($arr);
        }
        if ($params['withnumbers'] !== false) {
            $arr = remove_withnumbers($arr);
        }
        return $arr;
    }
}

if ( ! function_exists('array_clean') ) {
    function array_clean($arr)
    {
        return preg_grep("/^\s*\z/", array_filter(array_map('trim', array_unique($arr))), PREG_GREP_INVERT);
    }
}

if ( ! function_exists('html_clean') ) {
    function html_clean($html)
    {
        remove_empty_html_tags(remove_html_attributes($html));
    }
}

if ( ! function_exists('attr_remove') ) {
    function attr_remove($html)
    {
        $html = preg_replace("/id='.*?'/", '', $html);
        $html = preg_replace("/id=\".*?\"/", '', $html);
        $html = preg_replace("/class='.*?'/", '', $html);
        $html = preg_replace("/class=\".*?\"/", '', $html);
        $html = preg_replace("/style='.*?'/", '', $html);
        $html = preg_replace("/style=\".*?\"/", '', $html);
        $html = preg_replace("/itemscope='.*?'/", '', $html);
        $html = preg_replace("/itemscope=\".*?\"/", '', $html);
        $html = preg_replace("/itemtype='.*?'/", '', $html);
        $html = preg_replace("/itemtype=\".*?\"/", '', $html);
        $html = preg_replace("/itemprop='.*?'/", '', $html);
        $html = preg_replace("/itemprop=\".*?\"/", '', $html);
        $html = preg_replace("/role='.*?'/", '', $html);
        $html = preg_replace("/role=\".*?\"/", '', $html);
        $html = preg_replace("/target='.*?'/", '', $html);
        $html = preg_replace("/target=\".*?\"/", '', $html);
        $html = preg_replace("/aria.*?='..*?'/", '', $html);
        $html = preg_replace("/aria.*?=\".*?\"/", '', $html);
        $html = preg_replace("/data.*?='..*?'/", '', $html);
        $html = preg_replace("/data.*?=\".*?\"/", '', $html);
        return $html;
    }
}

if ( ! function_exists('get_doc') ) {
    function get_doc($html)
    {
        $html = clean(html_entity_decode($html, ENT_QUOTES | ENT_XML1, 'UTF-8'));
        $doc = new DOMDocument;
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        return $doc;
    }
}

if ( ! function_exists('is_inline') ) {
    function is_inline($parent)
    {
        if ($parent->tagName == 'strong' ||
            $parent->tagName == 'b' ||
            $parent->tagName == 'em' ||
            $parent->tagName == 'i' ||
            $parent->tagName == 'u') {
            return true;
        } else {
            return false;
        }
    }
}

if ( ! function_exists('is_block') ) {
    function is_block($parent)
    {
        if ($parent->tagName == 'body' ||
            $parent->tagName == 'div' ||
            $parent->tagName == 'p' ||
            $parent->tagName == 'li' ||
            $parent->tagName == 'th' ||
            $parent->tagName == 'td' ||
            $parent->tagName == 'dt' ||
            $parent->tagName == 'dd' ||
            $parent->tagName == 'q' ||
            $parent->tagName == 'blockquote' ||
            $parent->tagName == 'h1' ||
            $parent->tagName == 'h2' ||
            $parent->tagName == 'h3' ||
            $parent->tagName == 'h4' ||
            $parent->tagName == 'h5' ||
            $parent->tagName == 'h6' ||
            $parent->tagName == 'main' ||
            $parent->tagName == 'figure' ||
            $parent->tagName == 'figcaption') {
            return true;
        } else {
            return false;
        }
    }
}

if ( ! function_exists('get_parents') ) {
    function get_parents($a)
    {
        $block = '';
        $inline = '';

        $parent = $a->parentNode;

        if (is_block($parent)) {
            $block = $parent->tagName;
        } else {
            if (is_block($parent->parentNode)) {
                $block = $parent->parentNode->tagName;
            } else {
                if (is_block($parent->parentNode->parentNode)) {
                    $block = $parent->parentNode->parentNode->tagName;
                }
            }
        }

        if (is_inline($parent) && is_block($parent) !== true) {
            $inline = $parent->tagName;
        } else {
            if (is_inline($parent->parentNode)) {
                $inline = $parent->parentNode->tagName;
            } else {
                if (is_inline($parent->parentNode->parentNode)) {
                    $inline = $parent->parentNode->parentNode->tagName;
                }
            }
        }

        return [
            'block' => $block,
            'inline' => $inline,
        ];

    }
}

if ( ! function_exists('get_node_html') ) {
    function get_node_html(DOMNode $element)
    {
        $innerHTML = "";
        $children  = $element->childNodes;

        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }
}

if ( ! function_exists('get_array_key') ) {
    function get_array_key($needle, $haystack)
    {
        foreach ($haystack as $key => $value) {
            if (strpos($value, $needle) !== false) {
                return $key;
            }
        }
    }
}

////////////////////////////////////////////////////////////////////////////////

require_once 'classes/LoadTime.class.php';

////////////////////////////////////////////////////////////////////////////////

require_once 'classes/GetUrl.class.php';

////////////////////////////////////////////////////////////////////////////////

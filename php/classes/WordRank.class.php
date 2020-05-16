<?php

class WordRank
{
    public $api = [];

    private $sets = true;
    private $language = 'de';
    private $output = '';

    private $rankRelevance = [];

    private $urls = [];
    private $html = [];
    private $query = '';
    private $compare = false;

    private $rank = [];
    private $rankCompare = [];

    private $stopwords = [];

    private $calcByTags = [];
    private $calcByPositions = [];

    private $maxFrequencies = [];
    private $avgFrequencies = [];

    private $spamScores = [];

    private $titleSpamWords = [];
    private $titleSpamFactors = [];
    private $titleSpamFactorsWordsMax = 2;
    private $titleSpamFactorsMinFirstWord = 90;
    private $titleSpamFactorsWordsSimilarPercent = 55;

    private $qualityIssues = [];
    private $qualityIssuesFactors = [
        'top_count' => 30,
        'critical' => [
            'sw' => 3,
            'kw' => 50,
            'kd' => 4,
        ],
        'high' => [
            'sw' => 4,
            'kw' => 40,
            'kd' => 3,
        ],
        'medium' => [
            'sw' => 5,
            'kw' => 30,
            'kd' => 2,
        ],
        'low' => [
            'sw' => 6,
            'kw' => 20,
            'kd' => 1,
        ],
    ];

    private $sentences = [];
    private $sentencesCount = [];
    private $sentencesUnique = [];
    private $sentencesUniqueCount = [];
    private $sentencesUniqueWithCount = [];

    private $sentencesWithSpace = [];
    private $sentencesWithSpaceCount = [];
    private $sentencesWithSpaceUnique = [];
    private $sentencesWithSpaceUniqueCount = [];
    private $sentencesWithSpaceUniqueWithCount = [];

    private $sentencesUniqueMaxWordsCount = [];

    private $wordPhrases = [];
    private $wordPhrases2 = [];
    private $wordPhrases3 = [];
    #private $wordPhrases4 = [];
    #private $wordPhrases5 = [];
    #private $wordPhrases6 = [];
    #private $wordPhrases7 = [];
    #private $wordPhrases8 = [];
    #private $wordPhrases9 = [];
    #private $wordPhrases10 = [];
    private $wordPhrasesUnique = [];
    private $wordPhrasesUniqueWithoutStopwords = [];

    private $compareKeywords = [];
    private $compareKeywordsData = [];

    private $keywordAccordance = [];
    private $similarKeywords = [];
    private $similarKeywordsFactor = 0.2;

    private $compareFactors = [
        'keywords' => [
            'match_factor' => 80,
            'recommendations' => [
                'matches_precision' => 90,
                'mismatches_precision' => 55,
                'mismatches_important_precision' => 90,
            ],
        ],
    ];

    private $factors = [
        'all' => [
            'tag' => 16,
            'position' => 5,
            'repeat' => 3,
            'mention' => 1,
        ],
        'tag' => [
            'domain' => 120,
            'title' => 100,
            'h1' => 80,
            'h2' => 60,
            'path' => 60,
            'h3' => 40,
            'imgalt' => 40,
            'h4' => 20,
            'strong' => 20,
            'b' => 20,
            'em' => 20,
            'i' => 20,
            'dt' => 5,
            'h5' => 5,
            'th' => 5,
            'dd' => 2.5,
            'h6' => 2.5,
            'li' => 2.5,
            #'id' => 0.3125,
            'td' => 1,
            'p' => 1,
            'none' => 0.5,
        ],
        'position' => [
            'top_10_percentage' => 200,
            'quater_1' => 100,
            'quater_2' => 50,
            'quater_3' => 25,
            'quater_4' => 5,
        ],
    ];

    public function __construct($params = [])
    {
        if ( isset($params['sets']) ) {
            $this->sets = $params['sets'];
        }

        if ( isset($params['language']) ) {
            $this->language = $params['language'];
        }

        if ( isset($params['output']) ) {
            $this->output = $params['output'];
        }

        if ( isset($params['query']) ) {
            $this->query = $params['query'];
        }

        $this->urls = $params['urls'];
        $this->html = new HTML($this->urls);
        $this->compare = $params['compare'];

        if ($this->sets !== false) {
            $this->stopwords = get_stopwords($this->language);
            $this->setTitleSpamFactors();
            $this->setAvgFrequencies();
            $this->setMaxFrequencies();
            if (WORDTYPE == 'phrase' || WORDTYPE == 'combine') {
                if (ALLOW_WORDTYPE_PHRASE == true || ALLOW_WORDTYPE_COMBINE == true) {
                    $this->setSentences();
                    $this->setWordPhrases();
                }
            }
        }

        foreach ($this->urls as $key => $url) {
            $this->rank[$url] = $this->outputSingle($url, RANKTYPE, WORDTYPE);
        }

        if ($this->compare !== false) {
            $this->rankCompare = $this->outputCompare($this->rank);
        }

        if ($this->query !== '') {
            $this->setRankRelevance();
        }

        $this->setApi($this->output);
    }

    private function getPercentage($input)
    {
        $max = max($input);
        $percentage_arr = [];
        foreach ($input as $word => $value) {
            if ($value == 0) {
                $value = 1;
            }
            $percentage_arr[$word] = $value / ($max/100);
        }

        return $percentage_arr;
    }

    private function setAvgFrequencies()
    {
        foreach ($this->urls as $key => $url) {
            $this->setAvgFrequenzy($url);
        }
    }

    private function setAvgFrequenzy($url)
    {
        $frequenzy_words = [];

        foreach ($this->html->wordsArray[$url] as $word) {
            $frequenzy_words[$word] = round(substr_count(
                ' ' . $this->html->wordsString[$url] . ' ',
                ' ' . $word . ' '
            ) / ($this->html->wordsCount[$url] / 100 ), 2);
        }

        $frequenzy_words = array_filter($frequenzy_words);

        $this->avgFrequencies[$url] = round(array_sum($frequenzy_words) / count($frequenzy_words), 2);
    }

    private function setMaxFrequencies()
    {
        foreach ($this->urls as $key => $url) {
            $this->setMaxFrequenzy($url);
        }
    }

    private function setMaxFrequenzy($url)
    {
        $title = trim(str_replace('.', '', $this->html->htmlsTitle[$url]));
        $textwords = ' ' . $this->html->wordsString[$url] . ' ';

        $titlewords = [];
        foreach (@explode(' ', $title) as $word) {
            array_push($titlewords, $word);
        }
        $titlewords = array_unique($titlewords);

        $titlewords_frequenzy = [];
        foreach ($titlewords as $titleword) {
            $titlewords_frequenzy[$titleword] =
                substr_count($textwords, ' ' . $titleword . ' ') / ($this->html->wordsCount[$url] / 100);
        }

        $title_frequenzy_sum = 0;
        foreach ($titlewords_frequenzy as $word => $frequenzy) {
           $title_frequenzy_sum = $title_frequenzy_sum + $frequenzy;
        }

        $frequenzy_max = $title_frequenzy_sum / count($titlewords);

        $this->maxFrequencies[$url] = $frequenzy_max;
    }

    private function setTitleSpamFactors()
    {
        foreach ($this->urls as $key => $url) {
            $this->setTitleSpamFactor($url);
        }
    }

    private function setTitleSpamFactor($url)
    {
        $title_arr = [];
        foreach (@explode(' ', clean($this->html->htmlsTitle[$url])) as $word) {
            if (substr_count(' ' . $this->html->htmlsTitle[$url] . ' ', ' ' . $word . ' ') >= 2) {
                $word = $word;
                if (in_array($word, $title_arr)) {
                    $word = $word . '0';
                }
            }
            array_push($title_arr, $word);
        }

        $title_log = [];
        $count = count($title_arr);

        for ($i = 0; $i < $count; $i++) {
            $for_log = $count - $i;
            $title_log[$title_arr[$i]] = log10($for_log);
        }

        $title_factors = [];
        foreach ($title_log as $title_word => $value) {
            $title_factors[$title_word] = $value + 1;
        }

        $title_percentage = 0;
        $title_percentage_words = [];

        foreach ($title_arr as $word1) {
            foreach ($title_arr as $word2) {
                similar_text($word1, $word2, $perc);
                if ($word1 !== $word2) {
                    $title_percentage_words[$word1][$word2] = $perc;
                }
            }
        }

        $title_words = [];
        foreach ($title_percentage_words as $word => $word_array) {
            $count = 0;
            foreach ($word_array as $word_array_word => $word_array_word_value) {
                if ($word_array_word_value >= $this->titleSpamFactorsWordsSimilarPercent) {
                    $count = $count + 1;
                }
            }
            $title_words[$word] = $count;
        }

        $title_rank = [];
        foreach ($title_words as $word => $factor) {
            if ($factor >= $this->titleSpamFactorsWordsMax) {
                $title_rank[$word] = (($title_factors[$word] / $factor / count($title_words)) * 15) - 0.5;
            } else {
                $title_rank[$word] = 1;
            }
        }

        $this->titleSpamFactors[$url] = $title_rank;
    }

    private function setSentences()
    {
        foreach ($this->urls as $key => $url) {
            $this->setSentence($url);
        }
    }

    private function setSentence($url)
    {
        $text = $this->html->texts[$url];

        $sentences = [];
        foreach (explode('.', $text) as $phrase) {
        foreach (explode(',', $phrase) as $phrase) {
        foreach (explode(';', $phrase) as $phrase) {
        foreach (explode('!', $phrase) as $phrase) {
        foreach (explode('?', $phrase) as $phrase) {
        foreach (explode(':', $phrase) as $phrase) {
        foreach (explode('(', $phrase) as $phrase) {
        foreach (explode(')', $phrase) as $phrase) {
        foreach (explode('*', $phrase) as $phrase) {
        foreach (explode('"', $phrase) as $phrase) {
        foreach (explode("'", $phrase) as $phrase) {
        foreach (explode('[', $phrase) as $phrase) {
        foreach (explode(']', $phrase) as $phrase) {
        foreach (explode('{', $phrase) as $phrase) {
        foreach (explode('}', $phrase) as $phrase) {
        foreach (explode('„', $phrase) as $phrase) {
        foreach (explode('“', $phrase) as $phrase) {
        foreach (explode('<', $phrase) as $phrase) {
        foreach (explode('>', $phrase) as $phrase) {
        foreach (explode('–', $phrase) as $phrase) {
            if ($phrase !== '' && $phrase !== ' ') {
                array_push($sentences, clean($phrase));
            }
        }}}}}}}}}}}}}}}}}}}}

        $this->sentences[$url] = $sentences;
        $this->sentencesCount[$url] = count($sentences);
        $this->sentencesUnique[$url] = array_unique($sentences);
        $this->sentencesUniqueCount[$url] = count($this->sentencesUnique[$url]);

        $sentences_count_values = array_count_values($sentences);
        arsort($sentences_count_values);
        $this->sentencesUniqueWithCount[$url] = $sentences_count_values;

        $phrases = [];
        foreach ($sentences as $sentence) {
            if (strpos($sentence, ' ') !== false) {
                array_push($phrases, $sentence);
            }
        }

        $this->sentencesWithSpace[$url] = array_filter($phrases);
        $this->sentencesWithSpaceCount[$url] = count($phrases);
        $this->sentencesWithSpaceUnique[$url] = array_filter(array_unique($phrases));
        $this->sentencesWithSpaceUniqueCount[$url] = count($this->sentencesWithSpace[$url]);

        $phrases_count_values = array_count_values($this->sentencesWithSpace[$url]);
        arsort($phrases_count_values);
        $this->sentencesWithSpaceUniqueWithCount[$url] = $phrases_count_values;

        $words_count = [];
        foreach ($this->sentencesUnique[$url] as $sentence) {
            if (strpos($sentence, ' ') !== false) {
                $count = substr_count($sentence, ' ');
                array_push($words_count, $count);
            }
        }

        $this->sentencesUniqueMaxWordsCount[$url] = max($words_count) + 1;
    }

    private function setWordPhrases()
    {
        foreach ($this->urls as $key => $url) {
            $this->setWordPhrase($url);
        }
    }

    private function setWordPhrase($url)
    {
        $word_string = $this->html->wordsString[$url];

        $starts = [];
        foreach (explode(' ', $word_string) as $word) {
            array_push($starts, $word);
        }

        $phrases_2 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_2[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ]
            ;
        }
        $this->wordPhrases2[$url] = $this->getPhrasesCount($url, $phrases_2, 1);

        $phrases_3 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_3[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ] . ' ' .
                $starts[ ($i+2) ]
            ;
        }
        $this->wordPhrases3[$url] = $this->getPhrasesCount($url, $phrases_3, 2);

        /*
        $phrases_4 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_4[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ] . ' ' .
                $starts[ ($i+2) ] . ' ' .
                $starts[ ($i+3) ]
            ;
        }
        $this->wordPhrases4[$url] = $this->getPhrasesCount($url, $phrases_4, 3);

        $phrases_5 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_5[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ] . ' ' .
                $starts[ ($i+2) ] . ' ' .
                $starts[ ($i+3) ] . ' ' .
                $starts[ ($i+4) ]
            ;
        }
        $this->wordPhrases5[$url] = $this->getPhrasesCount($url, $phrases_5, 4);

        $phrases_6 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_6[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ] . ' ' .
                $starts[ ($i+2) ] . ' ' .
                $starts[ ($i+3) ] . ' ' .
                $starts[ ($i+4) ] . ' ' .
                $starts[ ($i+5) ]
            ;
        }
        $this->wordPhrases6[$url] = $this->getPhrasesCount($url, $phrases_6, 5);

        $phrases_7 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_7[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ] . ' ' .
                $starts[ ($i+2) ] . ' ' .
                $starts[ ($i+3) ] . ' ' .
                $starts[ ($i+4) ] . ' ' .
                $starts[ ($i+5) ] . ' ' .
                $starts[ ($i+6) ]
            ;
        }
        $this->wordPhrases7[$url] = $this->getPhrasesCount($url, $phrases_7, 6);

        $phrases_8 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_8[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ] . ' ' .
                $starts[ ($i+2) ] . ' ' .
                $starts[ ($i+3) ] . ' ' .
                $starts[ ($i+4) ] . ' ' .
                $starts[ ($i+5) ] . ' ' .
                $starts[ ($i+6) ] . ' ' .
                $starts[ ($i+7) ]
            ;
        }
        $this->wordPhrases8[$url] = $this->getPhrasesCount($url, $phrases_8, 7);

        $phrases_9 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_9[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ] . ' ' .
                $starts[ ($i+2) ] . ' ' .
                $starts[ ($i+3) ] . ' ' .
                $starts[ ($i+4) ] . ' ' .
                $starts[ ($i+5) ] . ' ' .
                $starts[ ($i+6) ] . ' ' .
                $starts[ ($i+7) ] . ' ' .
                $starts[ ($i+8) ]
            ;
        }
        $this->wordPhrases9[$url] = $this->getPhrasesCount($url, $phrases_9, 8);

        $phrases_10 = [];
        for ( $i = 0; $i < count($starts); $i++ ) {
            @$phrases_10[] =
                $starts[$i] . ' ' .
                $starts[ ($i+1) ] . ' ' .
                $starts[ ($i+2) ] . ' ' .
                $starts[ ($i+3) ] . ' ' .
                $starts[ ($i+4) ] . ' ' .
                $starts[ ($i+5) ] . ' ' .
                $starts[ ($i+6) ] . ' ' .
                $starts[ ($i+7) ] . ' ' .
                $starts[ ($i+8) ] . ' ' .
                $starts[ ($i+9) ]
            ;
        }
        $this->wordPhrases10[$url] = $this->getPhrasesCount($url, $phrases_10, 9);
        */

        $word_phrases = array_merge(
            $this->wordPhrases2[$url],
            $this->wordPhrases3[$url] /*,
            $this->wordPhrases4[$url],
            $this->wordPhrases5[$url],
            $this->wordPhrases6[$url],
            $this->wordPhrases7[$url],
            $this->wordPhrases8[$url],
            $this->wordPhrases9[$url],
            $this->wordPhrases10[$url] */
        );

        $output = $word_phrases;
        arsort($output);

        $this->wordPhrases[$url] = $output;

        $word_phrases_unique = [];
        foreach ($output as $phrase => $value) {
            array_push($word_phrases_unique, $phrase);
        }
        $this->wordPhrasesUnique[$url] = array_unique(array_filter($word_phrases_unique));

        $word_phrases_unique_without_stopwords = [];
        foreach ($this->wordPhrasesUnique[$url] as $phrase) {
            $phrase_origin = clean($phrase);
            $phrase_replace = str_replace($this->stopwords, ' ', $phrase_origin);
            $phrase_replace = str_replace(' ', '', $phrase_replace);
            if ($phrase_replace !== '') {
                $one_character = false;
                foreach (@explode(' ', $phrase_origin) as $word) {
                    if (strlen($word) == 1) {
                        $one_character = true;
                    }
                }
                if ($one_character !== true) {
                    array_push($word_phrases_unique_without_stopwords, $phrase_origin);
                }
            }
        }
        $this->wordPhrasesUniqueWithoutStopwords[$url] = $word_phrases_unique_without_stopwords;
    }

    private function getPhrasesCount($url, $phrases, $spaces)
    {
        $phrases_count = [];
        $phrases_unique = array_unique($phrases);

        foreach ($phrases_unique as $phrase) {

            $phrase = $phrase;

            if ( isset($phrase) && $phrase !== '' ) {

                foreach ($this->sentencesWithSpaceUnique[$url] as $sentence) {

                    if ( isset($sentence) && $sentence !== '' ) {

                        if (isset($phrases_count[$phrase])) {
                            $phrases_count[$phrase] = $phrases_count[$phrase] + substr_count($sentence, $phrase);
                        } else {
                            $phrases_count[$phrase] = substr_count($sentence, $phrase);
                        }

                    }
                }

            }

        }

        $phrases_count_min_1 = [];
        foreach ($phrases_count as $phrase => $value) {
            if ($value >= 1) {
                if (substr_count($phrase, ' ') === $spaces) {
                    $phrases_count_min_1[$phrase] = $value;
                }
            }
        }

        arsort( $phrases_count_min_1 );

        return $phrases_count_min_1;
    }

    private function calcTag($url, $phrase, $tag, $factor, $max = 0)
    {
        foreach ($this->html->tags[$url][$tag] as $tag) {
            $count = count_words($tag, $phrase);
            if ($count >= 1) {
                $score = 1;
                if ($max == 0) {
                    $score = $factor * $count;
                } else {
                    if ($count <= $max) {
                        $score = $factor * $count;
                    } else if ($count > $max) {
                        $score = $factor / $count;
                    }
                }
                if ( isset($this->calcByTags[$url][$phrase]) ) {
                    $this->calcByTags[$url][$phrase] = $this->calcByTags[$url][$phrase] + $score;
                } else {
                    $this->calcByTags[$url][$phrase] = $score;
                }
            }
        }
    }

    private function getRankByTag($url, $words, $percentage, $removes)
    {
        $factors = $this->factors['tag'];

        foreach ($words as $word) {

            if (strlen($word) >= 2) {

                $this->calcTag($url, $word, 'domain', $factors['domain'], 1);
                $this->calcTag($url, $word, 'title', $factors['title'], 1);
                $this->calcTag($url, $word, 'h1', $factors['h1'], 1);
                $this->calcTag($url, $word, 'path', $factors['path'], 1);

                $this->calcTag($url, $word, 'h2', $factors['h2']);
                $this->calcTag($url, $word, 'h3', $factors['h3']);
                $this->calcTag($url, $word, 'h4', $factors['h4']);
                $this->calcTag($url, $word, 'strong', $factors['strong']);
                $this->calcTag($url, $word, 'b', $factors['b']);
                $this->calcTag($url, $word, 'dt', $factors['dt']);
                $this->calcTag($url, $word, 'h5', $factors['h5']);
                $this->calcTag($url, $word, 'th', $factors['th']);
                $this->calcTag($url, $word, 'em', $factors['em']);
                $this->calcTag($url, $word, 'i', $factors['i']);
                $this->calcTag($url, $word, 'dd', $factors['dd']);
                $this->calcTag($url, $word, 'h6', $factors['h6']);
                $this->calcTag($url, $word, 'li', $factors['li']);
                $this->calcTag($url, $word, 'td', $factors['td']);
                $this->calcTag($url, $word, 'p', $factors['p']);
                #$this->calcTag($url, $word, 'id', $factors['id']);
                $this->calcTag($url, $word, 'imgalt', $factors['imgalt']);
                $this->calcTag($url, $word, 'div', $factors['none']);

            }

        }

        $output = $this->calcByTags[$url];
        arsort($output);
        unset($this->calcByTags[$url]);

        if ($removes !== false) {
            $output = remove(['stopwords' => true, 'withnumbers' => true], $output);
        }

        if ($percentage !== false) {
            $output = $this->getPercentage($output);
        }

        return $output;
    }

    private function calcPosition($url, $phrase, $quater, $factor)
    {
        $phrase_count = substr_count(' ' . clean($quater) . ' ', ' ' . clean($phrase) . ' ');
        $quater_count = substr_count(trim($quater), ' ') + 1;

        $score = 0;
        $frequenzy = 0;

        if ($phrase_count >= 1) {
            $frequenzy = $phrase_count / ($quater_count / 100);
            if ($frequenzy <= $this->maxFrequencies[$url]) {
                $score = $factor * $phrase_count;
            } else {
                $score = ($factor / $frequenzy) * $phrase_count;
            }
        }

        if ( isset($this->calcByPositions[$url][$phrase]) ) {
            $this->calcByPositions[$url][$phrase] = $this->calcByPositions[$url][$phrase] + $score;
        } else {
            $this->calcByPositions[$url][$phrase] = $score;
        }
    }

    private function getRankByPosition($url, $words, $percentage, $removes)
    {
        $words_top_10_percentage = '';
        $words_1_quater = '';
        $words_2_quater = '';
        $words_3_quater = '';
        $words_4_quater = '';

        $top_10_percentage = (int) $this->html->wordsCount[$url] / 10;
        $quater = (int) $this->html->wordsCount[$url] / 4;

        $i = 0;
        foreach ($this->html->wordsArray[$url] as $word) {

            if ( $i <= ($top_10_percentage) ) {
                $words_top_10_percentage .= ' ' . $word . ' ';
            }

            if ( $i <= ($quater) ) {
                $words_1_quater .= ' ' . $word . ' ';
            }

            if ( $i > ($quater) && $i <= ($quater * 2) ) {
                $words_2_quater .= ' ' . $word . ' ';
            }

            if ( $i > ($quater * 2) && $i <= ($quater * 3) ) {
                $words_3_quater .= ' ' . $word . ' ';
            }

            if ( $i > ($quater * 3) ) {
                $words_4_quater .= ' ' . $word . ' ';
            }

            $i++;
        }

        $factors = $this->factors['position'];

        foreach ($words as $word) {

            if (strlen($word) >= 2) {
                $this->calcPosition($url, $word, $top_10_percentage, $factors['top_10_percentage']);
                $this->calcPosition($url, $word, $words_1_quater, $factors['quater_1']);
                $this->calcPosition($url, $word, $words_2_quater, $factors['quater_2']);
                $this->calcPosition($url, $word, $words_3_quater, $factors['quater_3']);
                $this->calcPosition($url, $word, $words_4_quater, $factors['quater_4']);
            }

        }

        $output = $this->calcByPositions[$url];
        arsort($output);
        unset($this->calcByPositions[$url]);

        if ($removes !== false) {
            $output = remove(['stopwords' => true, 'withnumbers' => true], $output);
        }

        if ($percentage !== false) {
            $output = $this->getPercentage($output);
        }

        return $output;
    }

    private function getRankByMention($url, $words, $percentage, $removes)
    {
        $text = ' ' . clean($this->html->wordsString[$url]) . ' ';

        $mentions = [];
        foreach ($words as $word) {
            if (strlen($word) >= 2) {
                $mentions[$word] = substr_count($text, clean($word)) - substr_count($text, ' ' . $word . ' ');
            }
        }

        $output = $mentions;
        arsort($output);
        unset($mentions);

        if ($removes !== false) {
            $output = remove(['stopwords' => true, 'withnumbers' => true], $output);
        }

        if ($percentage !== false) {
            $output = $this->getPercentage($output);
        }

        if (array_sum($output) == (count($output) * 100)) {
            foreach ($output as $phrase => $value) {
                $output[$phrase] = 0;
            }
        }

        return $output;
    }

    private function getRankByRepeat($url, $words, $percentage, $removes)
    {
        $words_all = $this->html->wordsArray[$url];

        $repeats = [];
        foreach ($words as $phrase) {

            if (strlen($phrase) >= 2) {

                $i = 0;

                if (substr_count($phrase, ' ') == 0) {

                    foreach ($words_all as $word_all) {
                        if ($phrase == $word_all) {
                            if ( isset($repeats[$phrase]) ) {
                                $repeats[$phrase] = $repeats[$phrase] + $i;
                            } else {
                                $repeats[$phrase] = $i;
                            }
                            $i = 0;
                        }
                        $i++;
                    }

                } else {

                    foreach ($this->sentencesWithSpace[$url] as $sentence) {
                        if (substr_count(clean($sentence), clean($phrase)) >= 1) {
                            if ( isset($repeats[$phrase]) ) {
                                $repeats[$phrase] = $repeats[$phrase] + $i;
                            } else {
                                $repeats[$phrase] = $i;
                            }
                            $i = 0;
                        }
                        $i++;
                    }

                }

            }

        }

        $output = $repeats;
        arsort($output);
        unset($repeats);

        if ($removes !== false) {
            $output = remove(['stopwords' => true, 'withnumbers' => true], $output);
        }

        if ($percentage !== false) {
            $output = $this->getPercentage($output);
            $new_output = [];
            foreach ($output as $phrase => $value) {
                $new_output[$phrase] = 100 - $value;
            }
            $output = array_reverse($this->getPercentage($new_output));
        }

        return $output;
    }

    private function getRankByAll($url, $words, $type, $percentage, $removes)
    {
        $tag = $this->outputSingle($url, 'tag', $type, $percentage, $removes);
        $position = $this->outputSingle($url, 'position', $type, $percentage, $removes);
        $mention = $this->outputSingle($url, 'mention', $type, $percentage, $removes);
        $repeat = $this->outputSingle($url, 'repeat', $type, $percentage, $removes);

        $factors = $this->factors['all'];

        $rank = [];
        foreach ($words as $word) {
            $rank[$word] =
                (
                    (zero($tag, $word) * $factors['tag']) +
                    (zero($repeat, $word) * $factors['repeat']) +
                    (zero($mention, $word) * $factors['mention']) +
                    (zero($position, $word) * $factors['position'])
                ) / ($factors['tag'] + $factors['repeat'] + $factors['mention'] + $factors['position'])
            ;
        }

        arsort($rank);

        $output = $rank;
        arsort($output);
        unset($rank);

        if ($removes !== false) {
            $output = remove(['stopwords' => true, 'withnumbers' => true], $output);
        }

        if ($percentage !== false) {
            $output = $this->getPercentage($output);
        }

        $this->setSimilarKeywords($output, $url);

        $output_after_similer = [];
        foreach ($output as $keyword => $wordrank) {
            if ( isset($this->similarKeywords[$url][$keyword]['keywords_similar']) ) {
                $output_after_similer[$keyword] = $wordrank + ($this->similarKeywords[$url][$keyword]['wordrank'] * $this->similarKeywordsFactor);
            } else {
                $output_after_similer[$keyword] = $wordrank;
            }
        }

        arsort($output_after_similer);
        if ($percentage !== false) {
            $output = $this->getPercentage($output_after_similer);
        }

        $output_words = [];
        $i = 0;
        foreach ($output as $word => $value) {
            $output_words[$i] = $word;
            $i++;
        }

        $i = 0;
        foreach ($output as $word => $value) {

            $tt = false;
            $title_spam_words = $this->titleSpamFactors[$url];

            foreach ($title_spam_words as $title_word => $title_spam_factor) {
                similar_text($word, $title_word, $perc);
                if ( $word == $title_word || $perc >= $this->titleSpamFactorsMinFirstWord ) {
                    if ($title_spam_factor > 1) {
                        $tt = true;
                    }
                }
            }

            $sw_critical = false;
            $sw_high = false;
            $sw_medium = false;
            $sw_low = false;
            if ($i == 0) {
                $wordrank_summary = round(array_sum($output));
                $wr_factor = $wordrank_summary / $this->html->wordsUniqueCount[$url];
                if ($wr_factor <= $this->qualityIssuesFactors['critical']['sw']) {
                    $sw_critical = true;
                } else if ($wr_factor <= $this->qualityIssuesFactors['high']['sw']) {
                    $sw_high = true;
                } else if ($wr_factor <= $this->qualityIssuesFactors['medium']['sw']) {
                    $sw_medium = true;
                } else if ($wr_factor <= $this->qualityIssuesFactors['low']['sw']) {
                    $sw_low = true;
                }
            }

            $kw_critical = false;
            $kw_high = false;
            $kw_medium = false;
            $kw_low = false;

            if ($i <= $this->qualityIssuesFactors['top_count']) {

                $perc = $value / ($output[$output_words[($i+1)]] / 100);
                $perc = $perc - 100;

                if ($perc >= $this->qualityIssuesFactors['critical']['kw']) {
                    $kw_critical = true;
                } else if ($perc >= $this->qualityIssuesFactors['high']['kw']) {
                    $kw_high = true;
                } else if ($perc >= $this->qualityIssuesFactors['medium']['kw']) {
                    $kw_medium = true;
                } else if ($perc >= $this->qualityIssuesFactors['low']['kw']) {
                    $kw_low = true;
                }

            }

            $kd_critical = false;
            $kd_high = false;
            $kd_medium = false;
            $kd_low = false;

            $frequenzy = round(substr_count(
                ' ' . $this->html->wordsString[$url] . ' ',
                ' ' . $word . ' '
            ) / ($this->html->wordsCount[$url] / 100 ), 2);

            if ($frequenzy >= $this->qualityIssuesFactors['critical']['kd']) {
                $kd_critical = true;
            } else if ($frequenzy >= $this->qualityIssuesFactors['high']['kd']) {
                $kd_high = true;
            } else if ($frequenzy >= $this->qualityIssuesFactors['medium']['kd']) {
                $kd_medium = true;
            } else if ($frequenzy >= $this->qualityIssuesFactors['low']['kd']) {
                $kd_low = true;
            }

            $this->qualityIssues[$url][$word] = [
                'critical' => [
                    'tt' => $tt,
                    'sw' => $sw_critical,
                    'kw' => $kw_critical,
                    'kd' => $kd_critical,
                ],
                'high' => [
                    'tt' => false,
                    'sw' => $sw_high,
                    'kw' => $kw_high,
                    'kd' => $kd_high,
                ],
                'medium' => [
                    'tt' => false,
                    'sw' => $sw_medium,
                    'kw' => $kw_medium,
                    'kd' => $kd_medium,
                ],
                'low' => [
                    'tt' => false,
                    'sw' => $sw_low,
                    'kw' => $kw_low,
                    'kd' => $kd_low,
                ],
            ];

            $i++;

        }

        $this->setSpamScores($output, $url);

        return $output;
    }

    private function setSpamScores($rank, $url)
    {
        foreach ($this->qualityIssues as $urls => $keywords) {
            if ($urls == $url) {
                foreach ($keywords as $keyword => $issues) {
                    $wr_factor = $rank[$keyword] / 100;
                    foreach ($issues as $priority => $names) {
                        foreach ($names as $name => $is) {
                            if ($is !== false) {
                                if ($priority == 'critical') {
                                    if (isset($this->spamScores[$url])) {
                                        $this->spamScores[$url] = $this->spamScores[$url] + ($wr_factor * 10);
                                    } else {
                                        $this->spamScores[$url] = ($wr_factor * 10);
                                    }
                                } else if ($priority == 'high') {
                                    if (isset($this->spamScores[$url])) {
                                        $this->spamScores[$url] = $this->spamScores[$url] + ($wr_factor * 5);
                                    } else {
                                        $this->spamScores[$url] = ($wr_factor * 5);
                                    }
                                } else if ($priority == 'medium') {
                                    if (isset($this->spamScores[$url])) {
                                        $this->spamScores[$url] = $this->spamScores[$url] + ($wr_factor * 3);
                                    } else {
                                        $this->spamScores[$url] = ($wr_factor * 3);
                                    }
                                } else if ($priority == 'low') {
                                    if (isset($this->spamScores[$url])) {
                                        $this->spamScores[$url] = $this->spamScores[$url] + ($wr_factor * 1);
                                    } else {
                                        $this->spamScores[$url] = ($wr_factor * 1);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function outputSingle($url, $type, $words = 'word', $percentage = true, $removes = true)
    {
        $output = [];
        $prases = [];

        if ($words == 'word') {
            $prases = $this->html->wordsUnique[$url];
        } else if ($words == 'phrase') {
            $prases = $this->wordPhrasesUniqueWithoutStopwords[$url];
        }

        if ($type == 'tag') {
            if ($words == 'combine') {
                $output = array_merge(
                    $this->getRankByTag($url, $this->html->wordsUnique[$url], $percentage, $removes),
                    $this->getRankByTag($url, $this->wordPhrasesUniqueWithoutStopwords[$url], $percentage, $removes)
                );
                arsort($output);
            } else {
                $output = $this->getRankByTag($url, $prases, $percentage, $removes);
            }
        } else if ($type == 'position') {
            if ($words == 'combine') {
                $output = array_merge(
                    $this->getRankByPosition($url, $this->html->wordsUnique[$url], $percentage, $removes),
                    $this->getRankByPosition($url, $this->wordPhrasesUniqueWithoutStopwords[$url], $percentage, $removes)
                );
                arsort($output);
            } else {
                $output = $this->getRankByPosition($url, $prases, $percentage, $removes);
            }
        } else if ($type == 'mention') {
            if ($words == 'combine') {
                $output = array_merge(
                    $this->getRankByMention($url, $this->html->wordsUnique[$url], $percentage, $removes),
                    $this->getRankByMention($url, $this->wordPhrasesUniqueWithoutStopwords[$url], $percentage, $removes)
                );
                arsort($output);
            } else {
                $output = $this->getRankByMention($url, $prases, $percentage, $removes);
            }
        } else if ($type == 'repeat') {
            if ($words == 'combine') {
                $output = array_merge(
                    $this->getRankByRepeat($url, $this->html->wordsUnique[$url], $percentage, $removes),
                    $this->getRankByRepeat($url, $this->wordPhrasesUniqueWithoutStopwords[$url], $percentage, $removes)
                );
                arsort($output);
            } else {
                $output = $this->getRankByRepeat($url, $prases, $percentage, $removes);
            }
        } else if ($type == 'all') {
            if ($words == 'combine') {
                $output = array_merge(
                    $this->getRankByAll($url, $this->html->wordsUnique[$url], $words, $percentage, $removes),
                    $this->getRankByAll($url, $this->wordPhrasesUniqueWithoutStopwords[$url], $words, $percentage, $removes)
                );
                arsort($output);
            } else {
                $output = $this->getRankByAll($url, $prases, $words, $percentage, $removes);
            }
        }

        return $output;
    }

    private function setCompareKeywords($rank)
    {
        foreach ($rank as $url => $keywords) {

            $wordrank_without_spam = round(array_sum($keywords));
            $wordrank = round($wordrank_without_spam - (($wordrank_without_spam / 100) * $this->spamScores[$url]));

            foreach ($keywords as $keyword => $value) {
                $factor = $wordrank * $value;
                if ( isset($this->compareKeywords[$keyword]) ) {
                    $this->compareKeywords[$keyword] = $this->compareKeywords[$keyword] + $factor;
                } else {
                    $this->compareKeywords[$keyword] = $factor;
                }
            }

        }
    }

    private function getCompareKeywords()
    {
        $output = $this->compareKeywords;
        arsort($output);

        return $this->getPercentage($output);
    }

    private function getCompareByRecommendations($rank, $output)
    {
        $competitors_urls_count = (count($this->urls)) - 1;
        $compare_factors = $this->compareFactors;

        $wordranks = [];
        foreach ($rank as $url => $values) {
            $wordranks[$url] = array_sum($values);
        }

        $wordranks_factor = [];
        foreach ($wordranks as $url => $wordrank) {
            if ($url !== $this->urls[0]) {
                $wordranks_factor[$url] = ($wordrank / (max($wordranks) / 100)) / 100;
            }
        }

        /*********/
        $keyword_count = [];
        foreach ($rank[$this->urls[0]] as $keyword => $value) {
            foreach ($rank as $url => $keywords) {
                if ($url !== $this->urls[0]) {
                    if ( array_key_exists($keyword, $keywords) ) {
                        if ( isset($keyword_count[$keyword]) ) {
                            $keyword_count[$keyword] = $keyword_count[$keyword] + $wordranks_factor[$url];
                        } else {
                            $keyword_count[$keyword] = $wordranks_factor[$url];
                        }
                    } else {
                        if ( isset($keyword_count[$keyword]) ) {
                            $keyword_count[$keyword] = $keyword_count[$keyword] + 0;
                        } else {
                            $keyword_count[$keyword] = 0;
                        }
                    }
                }
            }
        }

        $keyword_recommendations_matches = [];
        foreach ($keyword_count as $keyword => $factor) {
            $perc = $factor / ((count($this->urls)-1) / 100);
            if ($perc >= $compare_factors['keywords']['match_factor']) {
                $keyword_recommendations_matches[$keyword] = true;
            } else {
                $keyword_recommendations_matches[$keyword] = false;
            }
        }
        /*********/

        /*********/
        $competitors_keywords = [];
        foreach ($rank as $url => $keywords) {
            if ($url !== $this->urls[0]) {
                foreach ($keywords as $keyword => $value) {
                    array_push($competitors_keywords, $keyword);
                }
            }
        }
        $competitors_keywords = array_unique($competitors_keywords);

        $competitors_keywords_count = [];
        foreach ($competitors_keywords as $key => $competitors_keyword) {
            for ($i = 1; $i < $competitors_urls_count+1; $i++) {
                foreach ($rank[$this->urls[$i]] as $keyword => $value) {
                    if ($competitors_keyword == $keyword) {
                        $factor = round((($wordranks_factor[$this->urls[$i]] * ($compare_factors['keywords']['match_factor'] / 100)) / $competitors_urls_count), 2);
                        if ( isset($competitors_keywords_count[$competitors_keyword]) ) {
                            $competitors_keywords_count[$competitors_keyword] = $competitors_keywords_count[$competitors_keyword] + $factor;
                        } else {
                            $competitors_keywords_count[$competitors_keyword] = $factor;
                        }
                    }
                }
            }
        }
        /*********/

        $output = $this->compareKeywords;
        arsort($output);

        $output = $this->getPercentage($output);

        /*********/
        $recommendations_matches = [];
        foreach ($output as $phrase => $value) {
            if (isset($keyword_recommendations_matches[$phrase])) {
                array_push($recommendations_matches, $phrase);
            }
        }

        $recommendations_mismatches = [];
        foreach ($output as $phrase => $value) {
            if (isset($competitors_keywords_count[$phrase])) {
                if ($competitors_keywords_count[$phrase] >= 0.5) {
                    array_push($recommendations_mismatches, $phrase);
                }
            }
        }
        /*********/

        $compare_factors_recommendations = $compare_factors['keywords']['recommendations'];

        /*********/
        $recommendations_matches_nearly = [];
        foreach ($recommendations_matches as $recommendations_match_keyword) {
            foreach ($rank[$this->urls[0]] as $keyword => $value) {
                similar_text($keyword, $recommendations_match_keyword, $perc_match);
                if ($perc_match >= $compare_factors_recommendations['matches_precision']) {
                    array_push($recommendations_matches_nearly, $keyword);
                }
            }
        }

        $recommendations_mismatches_nearly = [];
        $recommendations_mismatches_important_nearly = [];
        foreach ($recommendations_mismatches as $recommendations_mismatch_keyword) {
            foreach ($competitors_keywords as $keyword) {
                similar_text($keyword, $recommendations_mismatch_keyword, $perc_match);
                if ($perc_match >= $compare_factors_recommendations['mismatches_important_precision']) {
                    array_push($recommendations_mismatches_important_nearly, $keyword);
                } else if ($perc_match >= $compare_factors_recommendations['mismatches_precision']) {
                    array_push($recommendations_mismatches_nearly, $keyword);
                }
            }
        }
        /*********/

        $this->setTopKeywordAccordance($output, $rank);

        return [
            'recommendations_matches_nearly' => $recommendations_matches_nearly,
            'recommendations_mismatches_nearly' => $recommendations_mismatches_nearly,
            'recommendations_mismatches_important_nearly' => $recommendations_mismatches_important_nearly,
        ];
    }

    private function outputCompare($rank)
    {
        $this->urls = array_unique($this->urls);
        $this->setCompareKeywords($rank);
        $output = $this->getCompareKeywords();

        $compare_by_recommendations = $this->getCompareByRecommendations($rank, $output);

        $output_with_data = [];
        foreach ($output as $phrase => $value) {
            $recommendations_match = false;
            if (in_array($phrase, $compare_by_recommendations['recommendations_matches_nearly'])) {
                $recommendations_match = true;
            }
            $recommendations_mismatch = false;
            if (in_array($phrase, $compare_by_recommendations['recommendations_mismatches_nearly'])) {
                $recommendations_mismatch = true;
            }
            $recommendations_mismatch_important = false;
            if (in_array($phrase, $compare_by_recommendations['recommendations_mismatches_important_nearly'])) {
                $recommendations_mismatch_important = true;
            }
            $output_with_data[$phrase] = [
                'wordrank' => $value,
                'recommendations' => [
                    'match' => $recommendations_match,
                    'mismatch' => $recommendations_mismatch,
                    'mismatch_important' => $recommendations_mismatch_important,
                ],
            ];
        }
        $this->compareKeywordsData = $output_with_data;

        return $output_with_data;
    }

    private function setRankScores()
    {
        foreach ($this->urls as $key => $url) {
            $rank_without_spam = round(array_sum($this->rank[$url]));
            $rank = round($rank_without_spam - (($rank_without_spam / 100) * $this->spamScores[$url]));
            $this->rankScores[$url] = [
                'rank' => $rank,
                'rank_without_spam' => $rank_without_spam,
                'spam_score' => round($this->spamScores[$url], 2),
            ];
        }
    }

    private function setRankRelevance()
    {
        /*
        $this->rankRelevance = [
            'query' => $this->query,
            'urls' => $this->urls,
        ];

        return $this->rankRelevance;
        */
    }

    private function setTopKeywordAccordance($compare_keywords, $rank_keywords)
    {
        $keywords = [];

        foreach ($compare_keywords as $keyword => $rank) {
            array_push($keywords, $keyword);
        }

        $top_0_1_percent_cluster = [];
        $top_1_percent_cluster = [];
        $top_10_percent_cluster = [];

        $count_compare_keywords = count($compare_keywords);

        $top_0_1_percent_count = 4;
        $top_1_percent_count = 9;
        $top_10_percent_count = 29;

        if ($count_compare_keywords >= 1000) {
            $top_0_1_percent_count = (round($count_compare_keywords / 1000)) + 1;
            $top_1_percent_count = round($count_compare_keywords / 100);
            $top_10_percent_count = round($count_compare_keywords / 10);
        }

        for ($i = 0; $i < $count_compare_keywords; $i++) {
            if ($i <= $top_0_1_percent_count) {
                array_push($top_0_1_percent_cluster, $keywords[$i]);
            }
            if ($i <= $top_1_percent_count) {
                array_push($top_1_percent_cluster, $keywords[$i]);
            }
            if ($i <= $top_10_percent_count) {
                array_push($top_10_percent_cluster, $keywords[$i]);
            }
        }

        $top_cluster = [
            'top_0_1_percent_cluster' => $top_0_1_percent_cluster,
            'top_1_percent_cluster' => $top_1_percent_cluster,
            'top_10_percent_cluster' => $top_10_percent_cluster,
        ];

        $top_5_keywords = [];
        foreach ($rank_keywords as $url => $keywords) {
            $i = 0;
            $top_5_keywords[$url] = [];
            foreach ($keywords as $keyword => $rank) {
                if ($i <= 9) {
                    $top_5_keywords[$url][$keyword] = $rank;
                }
                $i++;
            }
        }

        foreach ($top_5_keywords as $url => $keywords) {
            foreach ($keywords as $keyword => $rank) {
                foreach ($top_0_1_percent_cluster as $key => $keyword_compare) {
                    similar_text($keyword, $keyword_compare, $perc);
                    if ($perc >= 55) {
                        $perc = $perc * 100;
                        $top_5_keywords[$url][$keyword] = $top_5_keywords[$url][$keyword] + $perc;
                    }
                }
                foreach ($top_1_percent_cluster as $key => $keyword_compare) {
                    similar_text($keyword, $keyword_compare, $perc);
                    if ($perc >= 70) {
                        $perc = $perc * 10;
                        $top_5_keywords[$url][$keyword] = $top_5_keywords[$url][$keyword] + $perc;
                    }
                }
                foreach ($top_10_percent_cluster as $key => $keyword_compare) {
                    similar_text($keyword, $keyword_compare, $perc);
                    if ($perc >= 85) {
                        $top_5_keywords[$url][$keyword] = $top_5_keywords[$url][$keyword] + $perc;
                    }
                }
            }
        }

        $top_5_keywords_accordance = [];
        foreach ($top_5_keywords as $url => $keywords) {
            $top_5_keywords_accordance[$url] = array_sum($keywords);
        }

        $one_percentage = max($top_5_keywords_accordance) / 100;
        foreach ($top_5_keywords_accordance as $url => $score) {
            $top_5_keywords_accordance[$url] = $score / $one_percentage;
        }

        $this->keywordAccordance = $top_5_keywords_accordance;
    }

    private function setSimilarKeywords($rank, $url)
    {
        $keywords_combine = [];

        foreach ($rank as $keyword => $wordrank) {
            $keywords_combine[$url][$keyword] = [];
        }

        foreach ($rank as $keyword => $wordrank) {
            foreach ($keywords_combine[$url] as $keyword_to_combine => $wordrank_to_combine) {
                similar_text($keyword, $keyword_to_combine, $perc);
                if ($perc >= 83 && $keyword !== $keyword_to_combine) {
                    $keywords_combine[$url][$keyword]['keywords_similar'][$keyword_to_combine] = $rank[$keyword_to_combine];
                    if ( ! isset($keyword, $keywords_combine[$url][$keyword]['keywords_similar'][$keyword]) ) {
                        $keywords_combine[$url][$keyword]['keywords_similar'][$keyword] = $rank[$keyword];
                    }
                }
            }
        }

        foreach ($keywords_combine[$url] as $keyword => $keywords_to_combine) {
            #pre($keywords_to_combine);
            if ( isset($keywords_to_combine['keywords_similar']) ) {
                $max = max($keywords_combine[$url][$keyword]['keywords_similar']);
                $max_keyword = array_keys($keywords_combine[$url][$keyword]['keywords_similar'], max($keywords_combine[$url][$keyword]['keywords_similar']));
                $sum = array_sum($keywords_combine[$url][$keyword]['keywords_similar']);
                $keywords_combine[$url][$keyword]['wordrank'] = $sum;
            }
        }

        $this->similarKeywords[$url] = $keywords_combine[$url];
    }

    private function setApi($type = '')
    {
        $date = new DateTime();
        $current_timestamp = $date->format('Y-m-d H:i:s');

        $this->setRankScores();

        /*********/
        $api = [
            'wordrank' => [
                'current_timestamp' => $current_timestamp,
                'language' => $this->language,
                'urls' => $this->urls,
                'rank' => [],
                'rank_compare' => [],
                #'rank_relevance' => [],
            ]
        ];

        /*
        if ( ! empty($this->rankRelevance) ) {
            $api['wordrank']['rank_relevance'] = $this->rankRelevance;
        }
        */

        foreach ($this->urls as $key => $url) {

            $phrases_count = 0;
            if ( ! empty($this->wordPhrases[$url]) ) {
                $phrases_count = count($this->wordPhrases[$url]);
            }

            $phrases_unique_count = 0;
            if ( ! empty($this->wordPhrasesUnique[$url]) ) {
                $phrases_unique_count = count($this->wordPhrasesUnique[$url]);
            }

            $sentences_count = 0;
            if ( ! empty($this->sentences[$url]) ) {
                $sentences_count = count($this->sentences[$url]);
            }

            $sentences_unique_count = 0;
            if ( ! empty($this->sentencesUnique[$url]) ) {
                $sentences_unique_count = count($this->sentencesUnique[$url]);
            }

            $accordance = 0;
            if ( isset($this->keywordAccordance[$url]) ) {
                $accordance = round($this->keywordAccordance[$url], 2);
            }

            /*********/
            $api['wordrank']['rank'][$url] = [
                'statistics' => [
                    'wordrank' => $this->rankScores[$url]['rank'],
                    'wordrank_without_spam' => $this->rankScores[$url]['rank_without_spam'],
                    'spam_score' => $this->rankScores[$url]['spam_score'],
                    'accordance' => $accordance,
                    'words' => $this->html->wordsCount[$url],
                    'words_unique' => $this->html->wordsUniqueCount[$url],
                    'phrases' => $phrases_count,
                    'phrases_unique' => $phrases_unique_count,
                    'sentences' => $sentences_count,
                    'sentences_unique' => $sentences_unique_count,
                    'keywords_count' => count($this->rank[$url]),
                    'keywords_density_average' => round($this->avgFrequencies[$url], 2),
                    'spam_signals' => [
                        'count' => 0,
                        'priority' => [
                            'critical' => [
                                'count' => 0,
                                'type' => [
                                    'tt' => 0,
                                    'sw' => 0,
                                    'kw' => 0,
                                    'kd' => 0,
                                ],
                            ],
                            'high' => [
                                'count' => 0,
                                'type' => [
                                    'tt' => 0,
                                    'sw' => 0,
                                    'kw' => 0,
                                    'kd' => 0,
                                ],
                            ],
                            'medium' => [
                                'count' => 0,
                                'type' => [
                                    'tt' => 0,
                                    'sw' => 0,
                                    'kw' => 0,
                                    'kd' => 0,
                                ],
                            ],
                            'low' => [
                                'count' => 0,
                                'type' => [
                                    'tt' => 0,
                                    'sw' => 0,
                                    'kw' => 0,
                                    'kd' => 0,
                                ],
                            ],
                        ],
                    ],
                ],
                'keywords' => [],
            ];
        }

        foreach ($this->rank as $url => $keywords) {

            $api['wordrank']['rank'][$url]['keywords'] = $keywords;

            foreach ($keywords as $keyword => $wordrank) {

                /************/
                $count_spam_signals_critical = count(array_filter($this->qualityIssues[$url][$keyword]['critical']));
                $count_spam_signals_high = count(array_filter($this->qualityIssues[$url][$keyword]['high']));
                $count_spam_signals_medium = count(array_filter($this->qualityIssues[$url][$keyword]['medium']));
                $count_spam_signals_low = count(array_filter($this->qualityIssues[$url][$keyword]['low']));
                $count_spam_signals =
                    $count_spam_signals_critical +
                    $count_spam_signals_high +
                    $count_spam_signals_medium +
                    $count_spam_signals_low;

                $api['wordrank']['rank'][$url]['statistics']['spam_signals']['count'] = $api['wordrank']['rank'][$url]['statistics']['spam_signals']['count'] + $count_spam_signals;
                $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['count'] = $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['count'] + $count_spam_signals_critical;
                $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['count'] = $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['count'] + $count_spam_signals_high;
                $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['count'] = $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['count'] + $count_spam_signals_medium;
                $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['count'] = $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['count'] + $count_spam_signals_low;

                $critical_tt = $this->qualityIssues[$url][$keyword]['critical']['tt'];
                $high_tt = $this->qualityIssues[$url][$keyword]['high']['tt'];
                $medium_tt = $this->qualityIssues[$url][$keyword]['medium']['tt'];
                $low_tt = $this->qualityIssues[$url][$keyword]['low']['tt'];
                $critical_sw = $this->qualityIssues[$url][$keyword]['critical']['sw'];
                $high_sw = $this->qualityIssues[$url][$keyword]['high']['sw'];
                $medium_sw = $this->qualityIssues[$url][$keyword]['medium']['sw'];
                $low_sw = $this->qualityIssues[$url][$keyword]['low']['sw'];
                $critical_kw = $this->qualityIssues[$url][$keyword]['critical']['kw'];
                $high_kw = $this->qualityIssues[$url][$keyword]['high']['kw'];
                $medium_kw = $this->qualityIssues[$url][$keyword]['medium']['kw'];
                $low_kw = $this->qualityIssues[$url][$keyword]['low']['kw'];
                $critical_kd = $this->qualityIssues[$url][$keyword]['critical']['kd'];
                $high_kd = $this->qualityIssues[$url][$keyword]['high']['kd'];
                $medium_kd = $this->qualityIssues[$url][$keyword]['medium']['kd'];
                $low_kd = $this->qualityIssues[$url][$keyword]['low']['kd'];

                if ($critical_tt !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['type']['tt'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['type']['tt'] + 1;
                }
                if ($critical_sw !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['type']['sw'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['type']['sw'] + 1;
                }
                if ($critical_kw !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['type']['kw'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['type']['kw'] + 1;
                }
                if ($critical_kd !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['type']['kd'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['critical']['type']['kd'] + 1;
                }
                if ($high_tt !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['type']['tt'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['type']['tt'] + 1;
                }
                if ($high_sw !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['type']['sw'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['type']['sw'] + 1;
                }
                if ($high_kw !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['type']['kw'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['type']['kw'] + 1;
                }
                if ($high_kd !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['type']['kd'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['high']['type']['kd'] + 1;
                }
                if ($medium_tt !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['type']['tt'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['type']['tt'] + 1;
                }
                if ($medium_sw !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['type']['sw'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['type']['sw'] + 1;
                }
                if ($medium_kw !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['type']['kw'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['type']['kw'] + 1;
                }
                if ($medium_kd !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['type']['kd'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['medium']['type']['kd'] + 1;
                }
                if ($low_tt !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['type']['tt'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['type']['tt'] + 1;
                }
                if ($low_sw !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['type']['sw'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['type']['sw'] + 1;
                }
                if ($low_kw !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['type']['kw'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['type']['kw'] + 1;
                }
                if ($low_kd !== false) {
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['type']['kd'] =
                    $api['wordrank']['rank'][$url]['statistics']['spam_signals']['priority']['low']['type']['kd'] + 1;
                }

                /************/

                $similar_keywords = [];
                if ( isset($this->similarKeywords[$url][$keyword]['keywords_similar']) ) {
                    $similar_keywords = $this->similarKeywords[$url][$keyword]['keywords_similar'];
                }

                $api['wordrank']['rank'][$url]['keywords'][$keyword] = [
                    'wordrank' => round($wordrank, 2),
                    'density' => round(substr_count(
                            ' ' . $this->html->wordsString[$url] . ' ',
                            ' ' . $keyword . ' '
                        ) / ($this->html->wordsCount[$url] / 100 ), 2),
                    'characters' => strlen($keyword),
                    'words_count' => (substr_count($keyword, ' ') + 1),
                    'spam_signals' => [
                        'count' => $count_spam_signals,
                        'priority' => [
                            'critical' => [
                                'count' => $count_spam_signals_critical,
                                'type' => [
                                    'tt' => $this->qualityIssues[$url][$keyword]['critical']['tt'],
                                    'sw' => $this->qualityIssues[$url][$keyword]['critical']['sw'],
                                    'kw' => $this->qualityIssues[$url][$keyword]['critical']['kw'],
                                    'kd' => $this->qualityIssues[$url][$keyword]['critical']['kd'],
                                ],
                            ],
                            'high' => [
                                'count' => $count_spam_signals_high,
                                'type' => [
                                    'tt' => $this->qualityIssues[$url][$keyword]['high']['tt'],
                                    'sw' => $this->qualityIssues[$url][$keyword]['high']['sw'],
                                    'kw' => $this->qualityIssues[$url][$keyword]['high']['kw'],
                                    'kd' => $this->qualityIssues[$url][$keyword]['high']['kd'],
                                ],
                            ],
                            'medium' => [
                                'count' => $count_spam_signals_medium,
                                'type' => [
                                    'tt' => $this->qualityIssues[$url][$keyword]['medium']['tt'],
                                    'sw' => $this->qualityIssues[$url][$keyword]['medium']['sw'],
                                    'kw' => $this->qualityIssues[$url][$keyword]['medium']['kw'],
                                    'kd' => $this->qualityIssues[$url][$keyword]['medium']['kd'],
                                ],
                            ],
                            'low' => [
                                'count' => $count_spam_signals_low,
                                'type' => [
                                    'tt' => $this->qualityIssues[$url][$keyword]['low']['tt'],
                                    'sw' => $this->qualityIssues[$url][$keyword]['low']['sw'],
                                    'kw' => $this->qualityIssues[$url][$keyword]['low']['kw'],
                                    'kd' => $this->qualityIssues[$url][$keyword]['low']['kd'],
                                ],
                            ],
                        ],
                    ],
                    'similar_keywords' => $similar_keywords,
                ];
            }
        }

        if ($this->compare !== false) {

            $api['wordrank']['rank_compare']['compare_url'] = $this->urls[0];

            $keywords_in_urls = [];
            foreach ($api['wordrank']['rank'] as $url => $data) {
                foreach ($data['keywords'] as $keyword => $data) {
                    $keywords_in_urls[$keyword] = [];
                }
            }

            foreach ($api['wordrank']['rank'] as $url => $data) {
                foreach ($data['keywords'] as $keyword => $data) {
                    array_push($keywords_in_urls[$keyword], $url);
                }
            }

            foreach ($this->rankCompare as $keyword => $data) {
                $api['wordrank']['rank_compare']['keywords'][$keyword] = [
                    'wordrank' => $data['wordrank'],
                    'characters' => strlen($keyword),
                    'words_count' => (substr_count($keyword, ' ') + 1),
                    'found_in_urls' => $keywords_in_urls[$keyword],
                    'recommendations' => [
                        'match' => $data['recommendations']['match'],
                        'mismatch' => $data['recommendations']['mismatch'],
                        'mismatch_important' => $data['recommendations']['mismatch_important'],
                    ],
                ];
            }

        }

        /*********/

        if ($this->output == 'json') {
            header('Content-type: application/json; charset=utf-8');
            $this->api = json_encode($api);
        } else {
            $this->api = $api;
        }
    }

}

<?php

class HTML
{
    public $urls = [];
    public $codes = [];
    public $helper = [];

    public $htmls = [];
    public $htmlsDomain = [];
    public $htmlsPath = [];
    public $htmlsTitle = [];
    public $htmlsBody = [];

    public $texts = [];

    public $wordsString = [];
    public $wordsArray = [];
    public $wordsCount = [];
    public $wordsUnique = [];
    public $wordsUniqueCount = [];
    public $wordsUniqueValues = [];

    public $tags = [];

    public function __construct($urls)
    {
        $this->urls = $urls;
        $this->helper = new Helper($this->urls);

        $this->codes = $this->helper->codes;

        $this->setHtmlsDomain();
        $this->setHtmlsPath();
        $this->setHtmlsTitle();
        $this->setHtmlsBody();
        $this->setHtmls();

        $this->setTexts();
        $this->setWords();
        $this->setTags();
    }

    private function setHtmls()
    {
        foreach ($this->urls as $key => $url) {
            $this->setHtml($url);
        }
    }

    private function setHtml($url)
    {
        $this->htmls[$url] =
            '<domain>' . $this->htmlsDomain[$url] . '</domain>' . "\n" .
            '<path>' . $this->htmlsPath[$url] . '</path>' . "\n" .
            '<title>' . $this->htmlsTitle[$url] . '</title>' . "\n" .
            '<body>' . $this->htmlsBody[$url] . '</body>' . "\n"
        ;
    }

    private function setHtmlsDomain()
    {
        foreach ($this->urls as $key => $url) {
            $this->setHtmlDomain($url);
        }
    }

    private function setHtmlDomain($url)
    {

        $this->htmlsDomain[$url] = $this->helper->clean($this->helper->urlDomains[$url]) . '. ';

    }

    private function setHtmlsPath()
    {
        foreach ($this->urls as $key => $url) {
            $this->setHtmlPath($url);
        }
    }

    private function setHtmlPath($url)
    {

        $this->htmlsPath[$url] = $this->helper->clean($this->helper->urlPaths[$url]) . '. ';

    }

    private function setHtmlsTitle()
    {
        foreach ($this->urls as $key => $url) {
            $this->setHtmlTitle($url);
        }
    }

    private function setHtmlTitle($url)
    {
        preg_match_all("/<title(.*?)>(.*?)<\/title>/", $this->codes[$url], $matches);
        $title = '';
        foreach ($matches[2] as $phrase) {
            $title .= $phrase . '.';
        }
        $this->htmlsTitle[$url] = $this->helper->clean($title) . '. ';
    }

    private function setHtmlsBody()
    {
        foreach ($this->urls as $key => $url) {
            $this->setHtmlBody($url);
        }
    }

    private function setHtmlBody($url)
    {
        $doc = $this->helper->getDom(strtolower($this->codes[$url]));
        $xpath = new DOMXpath($doc);

        $body = '';
        foreach ($xpath->evaluate('//body/node()') as $node) {
            $body .= $doc->saveHTML($node);
        }

        $body = preg_replace(
            "/<img(.*?)alt=\"(.*?)\"(.*?)>/is",
            "<imgalt>$2</imgalt>",
        $body);
        $body = preg_replace(
            "/<img(.*?)alt='(.*?)'(.*?)>/is",
            "<imgalt>$2</imgalt>",
        $body);

        $body = attr_remove($body);

        /*
        $body = preg_replace(
            '/<(.*?)id="(.*?)"(.*?)>/is',
            "<$1 $3><id>$2.</id>",
        $body);
        $body = preg_replace(
            "/<(.*?)id='(.*?)'(.*?)>/is",
            "<$1 $3><id>$2</id>",
        $body);
        */

        if ( strpos($body, '<h1') !== false ) {

            $strip_by_h1 = @explode('<h1', $body);
            $body = str_replace($strip_by_h1[0], '', $body);
            $body = $body . ' ' . $strip_by_h1[0];

        } else if ( strpos($body, '<h2') !== false ) {

            $strip_by_h2 = @explode('<h2', $body);
            $body = str_replace($strip_by_h2[0], '', $body);
            $body = $body . ' ' . $strip_by_h2[0];

        }

        $this->htmlsBody[$url] = str_replace([' – ', '– ', ' –', ' - ', '- ', ' -'], ' ', $body) . '.' . "\n";
    }

    private function setTexts()
    {
        foreach ($this->urls as $key => $url) {
            $this->setText($url);
        }
    }

    private function setText($url)
    {
        $text = $this->htmls[$url];

        $text = str_replace([
            '</h1>',
            '</h2>',
            '</h3>',
            '</h4>',
            '</h5>',
            '</h6>',
            '</li>',
            '</th>',
            '</td>',
            '</tr>',
            '</dl>',
            '</dt>',
            '</dd>',
            '</p>',
            '</div>',
            '</section>',
            '</article>',
        ], '. ', $text);

        $text = preg_replace("/<.*?>/", ' ', $text);

        $text = str_replace(array("\t", "\n", "
        "), ' ', $text);

        for ($i = 0; $i < substr_count($text, '  '); $i++) {
            $text = str_replace('  ', ' ', $text);
        }

        for ($i = 0; $i < substr_count($text, ' .'); $i++) {
            $text = str_replace(' .', '.', $text);
        }

        for ($i = 0; $i < substr_count($text, '..'); $i++) {
            $text = str_replace('..', '.', $text);
        }

        for ($i = 0; $i < substr_count($text, '  '); $i++) {
            $text = str_replace('  ', ' ', $text);
        }

        $this->texts[$url] = trim($text);
    }

    private function setWords()
    {
        foreach ($this->urls as $key => $url) {
            $this->setWord($url);
        }
    }

    private function setWord($url)
    {
        $words = $this->helper->clean($this->texts[$url]);

        $this->wordsString[$url] = $words;

        $words_array = [];
        foreach (@explode(' ', $words) as $word) {

            if ( ! empty($word) && $word !== ' ') {
                if ( strlen($word) >=2 ) {
                    $word = preg_replace("/\s/u", '', $word);
                    if ($word !== '') {
                        array_push($words_array, $word);
                    }
                }
            }
        }

        $this->wordsArray[$url] = $words_array;

        $this->wordsCount[$url] = count($this->wordsArray[$url]);

        $this->wordsUnique[$url] = array_unique($this->wordsArray[$url]);
        $this->wordsUniqueCount[$url] = count($this->wordsUnique[$url]);

        $wordsUniqueValues = array_count_values($this->wordsArray[$url]);
        arsort($wordsUniqueValues);
        $this->wordsUniqueValues[$url] = $wordsUniqueValues;
    }

    private function setTags()
    {
        foreach ($this->urls as $key => $url) {
            $this->setTag($url);
        }
    }

    private function setTag($url)
    {
        $doc = $this->helper->getDom(strtolower($this->codes[$url]));

        $description = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('meta') as $meta) {
            if ($meta->getAttribute('name') == 'description') {
                $description[$count] = $this->helper->clean($meta->getAttribute('content'));
            }
            $count++;
        }

        $domain = [];
        $count = 0;
        preg_match_all("/<domain(.*?)>(.*?)<\/domain>/", $this->htmls[$url], $matches);
        foreach ($matches[2] as $tag) {
            $domain[$count] = $this->helper->clean($tag);
            $count++;
        }

        $path = [];
        $count = 0;
        preg_match_all("/<path(.*?)>(.*?)<\/path>/", $this->htmls[$url], $matches);
        foreach ($matches[2] as $tag) {
            $path[$count] = $this->helper->clean($tag);
            $count++;
        }

        $title = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('title') as $tag) {
            $title[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $id = [];
        $count = 0;
        preg_match_all("/<id(.*?)>(.*?)<\/id>/", $this->htmls[$url], $matches);
        foreach ($matches[2] as $tag) {
            $id[$count] = $this->helper->clean($tag);
            $count++;
        }

        $imgalt = [];
        $count = 0;
        preg_match_all("/<imgalt(.*?)>(.*?)<\/imgalt>/", $this->htmls[$url], $matches);
        foreach ($matches[2] as $tag) {
            $imgalt[$count] = $this->helper->clean($tag);
            $count++;
        }

        $h1 = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('h1') as $tag) {
            $h1[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $h2 = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('h2') as $tag) {
            $h2[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $h3 = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('h3') as $tag) {
            $h3[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $h4 = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('h4') as $tag) {
            $h4[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $h5 = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('h5') as $tag) {
            $h5[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $h6 = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('h6') as $tag) {
            $h6[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $strong = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('strong') as $tag) {
            $strong[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $b = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('b') as $tag) {
            $b[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $em = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('em') as $tag) {
            $em[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $i = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('i') as $tag) {
            $i[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $dt = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('dt') as $tag) {
            $dt[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $dd = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('dd') as $tag) {
            $dd[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $th = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('th') as $tag) {
            $th[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $td = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('td') as $tag) {
            $td[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $li = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('li') as $tag) {
            $li[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $u = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('u') as $tag) {
            $u[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $p = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('p') as $tag) {
            $p[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $a = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('a') as $tag) {
            $a[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $q = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('q') as $tag) {
            $q[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        $blockquote = [];
        $count = 0;
        foreach ($doc->getELementsByTagName('blockquote') as $tag) {
            $blockquote[$count] = $this->helper->clean($tag->nodeValue);
            $count++;
        }

        /////

        $html = $this->htmls[$url];

        $html = preg_replace("/<\s*domain.+?<\s*\/\s*domain.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*path.+?<\s*\/\s*path.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*title.+?<\s*\/\s*title.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*imgalt.+?<\s*\/\s*imgalt.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*id.+?<\s*\/\s*id.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*h1.+?<\s*\/\s*h1.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*h2.+?<\s*\/\s*h2.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*h3.+?<\s*\/\s*h3.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*h4.+?<\s*\/\s*h4.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*h5.+?<\s*\/\s*h5.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*h6.+?<\s*\/\s*h6.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*th.+?<\s*\/\s*th.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*td.+?<\s*\/\s*td.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*li.+?<\s*\/\s*li.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*dt.+?<\s*\/\s*dt.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*dd.+?<\s*\/\s*dd.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*p.+?<\s*\/\s*p.*?>/si", ' ', $html);
        $html = preg_replace("/<\s*blockquote.+?<\s*\/\s*blockquote.*?>/si", ' ', $html);
        $html = preg_replace("/<.*?>/", ' ', $html);

        $div[0] = $this->helper->clean($this->helper->clean($html));

        /////

        $this->tags[$url] = [
            'domain' => array_clean($domain),
            'path' => array_clean($path),
            'title' => array_clean($title),
            'description' => array_clean($description),
            #'id' => array_clean($id),
            'imgalt' => array_clean($imgalt),
            'h1' => array_clean($h1),
            'h2' => array_clean($h2),
            'h3' => array_clean($h3),
            'h4' => array_clean($h4),
            'h5' => array_clean($h5),
            'h6' => array_clean($h6),
            'strong' => array_clean($strong),
            'b' => array_clean($b),
            'em' => array_clean($em),
            'i' => array_clean($i),
            'dt' => array_clean($dt),
            'dd' => array_clean($dd),
            'th' => array_clean($th),
            'td' => array_clean($td),
            'li' => array_clean($li),
            'u' => array_clean($u),
            'p' => array_clean($p),
            'a' => array_clean($a),
            'q' => array_clean($q),
            'blockquote' => array_clean($blockquote),
            'div' => array_clean($div),
        ];
    }

}

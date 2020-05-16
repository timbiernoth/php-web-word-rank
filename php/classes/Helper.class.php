<?php

class Helper
{
    public $urls = [];
    public $codes = [];

    public $urlPaths = [];
    public $urlPages = [];
    public $urlDomains = [];

    public $symbols = [];
    public $symbolsUrl = [];
    public $symbolsSign = [];
    public $symbolsFree = [];
    public $symbolsSentence = [];
    public $symbolsNavigation = [];

    public function __construct($urls)
    {
        foreach ($urls as $key => $url) {
            $this->urls[$key] = strtolower($url);
        }

        $this->setSymbols();
        $this->setCodes();
        $this->setUrls();
    }

    private function setCodes()
    {
        foreach ($this->urls as $key => $url) {
            $this->codes[$url] = $this->setCode($url);
        }
    }

    private function setCode($input)
    {
        #$code = file_get_contents($input);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $input);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        //curl_setopt($curl ,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($curl ,CURLOPT_USERAGENT,'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3B48b Safari/419.3');

        $code = curl_exec($curl);

        $code = preg_replace("/<\s*style.+?<\s*\/\s*style.*?>/si", '', $code);
        $code = preg_replace("/<\s*script.+?<\s*\/\s*script.*?>/si", '', $code);
        $code = preg_replace("/<\s*noscript.+?<\s*\/\s*noscript.*?>/si", '', $code);
        $code = preg_replace("/<!--.*?-->/si", '', $code);

        $doc = new DOMDocument;
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $code);
        $code = $doc->saveHTML();

        return $code;
    }

    private function setUrls()
    {
        foreach ($this->urls as $key => $url) {
            $this->setUrl($url);
        }
    }

    private function setUrl($url)
    {
        $url_origin = $url;

        $url = str_replace($this->symbolsUrl, '', $url);
        $url_split = @explode('/', $url);

        $this->urlDomains[$url_origin] = $url_split[0];
        $this->urlPages[$url_origin] = [];

        $i = 0;
        $path = '';
        $path_string = '';
        foreach ($url_split as $page) {
            if ($i >= 1) {
                array_push($this->urlPages[$url_origin], $page);
                $path .= $page;
                $path_string .= $page . ' ';
            }
            $i++;
        }

        $this->urlPaths[$url_origin] = $path_string;
    }

    public function getDom($input)
    {
        $doc = new DOMDocument;
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $input);

        return $doc;
    }

    private function setSymbols()
    {
        $this->symbolsUrl = [
            'http://www.',
            'https://www.',
            'http://',
            'https://',
            '.html',
            '.htm',
            '.phps',
            '.php',
            '.aspx',
            '.asp',
            '.jpeg',
            '.jpg',
            '.png',
            '.gif',
            '.svg',
        ];

        $this->symbolsSign = [
            '©',
            '@',
            '€',
            '&amp;',
            '&quot;',
            '&lt;',
            '&rt;',
            '&nbsp;',
            '&gt;',
        ];

        $this->symbolsFree = [
            '\\',
            '+',
            '-',
            '_',
            '§',
            '%',
            '|',
            '~',
            '#',
            '^',
            '°',
            '•',
            '×',
            '♥',
            '♪',
            '‘',
            '❊',
        ];

        $this->symbolsSentence = [
            '.',
            ',',
            ';',
            '!',
            '?',
            ':',
            '(',
            ')',
            '[',
            ']',
            '{',
            '}',
            '–',
            '*',
            '"',
            "'",
            '„',
            '“',
            '<',
            '>',
            '”',
            '=',
            '´',
            '`',
            '/',
            '…',
        ];

        $this->symbolsNavigation = [
            '»',
            '«',
            '<',
            '>',
            '↑',
        ];

        $this->symbols = array_merge(
            $this->symbolsUrl,
            $this->symbolsSign,
            $this->symbolsFree,
            $this->symbolsSentence,
            $this->symbolsNavigation
        );
    }

    public function clean($input)
    {
        $input = str_replace($this->symbols, ' ', $input);

        return clean($input);
    }
}

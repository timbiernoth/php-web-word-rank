<?php

class GetUrl
{
    public $url = [];
    public $urls = [];
    public $query = '';
    public $compare = false;

    public $rankType = '';
    public $wordType = '';

    public $error = false;
    public $errorMassage = '';

    public function __construct($url)
    {
        $this->url = $url;
        $this->setUrl();
        $this->setQuery();
        $this->setCompare();
    }

    private function setUrl()
    {
        if ( empty($this->url) ) {

            if ( isset($_GET['url']) ) {
                $this->url = $_GET['url'];
            }

            if ( ! empty($this->url) ) {

                foreach ($this->url as $key => $url) {

                    if (
                        (
                            substr($url, 0, 8) === 'https://' ||
                            substr($url, 0, 7) === 'http://'
                        ) && str_replace(['https://', 'http://'], '', $url) !== ''
                    ) {
                        $this->urls[$key] = $url;
                    }

                }

                if ($this->rankType == '' && ALLOW_RANKTYPE !== false) {
                    if ( isset($_GET['rank']) && ! empty($_GET['rank']) ) {
                        $rank = $_GET['rank'];
                        if (
                            $rank == 'all' ||
                            $rank == 'tag' ||
                            $rank == 'position' ||
                            $rank == 'mention' ||
                            $rank == 'repeat' ||
                            $rank == 'part'
                        ) {
                            $this->rankType = $rank;
                        } else {
                            $this->error = true;
                            $this->errorMassage .= ' Falscher Rank-Typ angegeben.';
                        }
                    } else {
                        if (OVERWRITE_RANKTYPE !== '') {
                            $this->rankType = OVERWRITE_RANKTYPE;
                        } else {
                            $this->error = true;
                            $this->errorMassage .= ' Rank-Typ nicht angegeben.';
                        }
                    }
                }

                if ($this->wordType == '' && ALLOW_WORDTYPE !== false) {
                    if ( isset($_GET['word']) && ! empty($_GET['word']) ) {
                        $word = $_GET['word'];
                        if (
                            ($word == 'word' && ALLOW_WORDTYPE_WORD !== false) ||
                            ($word == 'phrase' && ALLOW_WORDTYPE_PHRASE !== false) ||
                            ($word == 'combine' && ALLOW_WORDTYPE_COMBINE !== false)
                        ) {
                            $this->wordType = $word;
                        } else {
                            $this->error = true;
                            $this->errorMassage .= ' Falscher Word-Typ angegeben.';
                        }
                    } else {
                        if (OVERWRITE_WORDTYPE !== '') {
                            $this->wordType = OVERWRITE_WORDTYPE;
                        } else {
                            $this->error = true;
                            $this->errorMassage .= ' Word-Typ nicht angegeben.';
                        }
                    }
                }

            } else {
                $this->error = true;
                $this->errorMassage .= ' Keine gültige URLs.';
            }

        }
    }

    private function setCompare()
    {
        if ( isset($_GET['compare']) && $_GET['compare'] == 'on' ) {
            $this->compare = true;
        }
    }

    private function setQuery()
    {
        if ( isset($_GET['query']) && $_GET['query'] !== '' && strlen($_GET['query']) >= 2) {
            $this->query = $_GET['query'];
        }
    }
}

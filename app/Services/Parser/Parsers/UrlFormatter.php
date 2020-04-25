<?php

namespace Coyote\Services\Parser\Parsers;

use Collective\Html\HtmlBuilder;

class UrlFormatter
{
    // http://daringfireball.net/2010/07/improved_regex_for_matching_urls
    private const REGEXP_URL = '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))#u';

    private const TITLE_LEN = 64;
    private const TITLE_DOTS = '[...]';

    /** @var string */
    private $host;
    /** @var HtmlBuilder */
    private $html;

    public function __construct(string $host, HtmlBuilder $html)
    {
        $this->host = $host;
        $this->html = $html;
    }

    public function parse(string $text): string
    {

        $processed = preg_replace_callback(
            self::REGEXP_URL,
            function ($match) {
                $url = $match[0];

                if (!preg_match('#^[\w]+?://.*?#i', $url)) {
                    $url = 'http://' . $url;
                }

                $title = $this->truncate(htmlspecialchars($match[0], ENT_QUOTES, 'UTF-8', false));

                return $this->html->link($url, $title);
            },
            $text
        );

        // regexp posiada buga, nie parsuje poprawnie URL-i jezeli zawiera on (
        // poki co nie mam rozwiazania na ten problem, dlatego zwracamy nieprzeprasowany tekst
        // w przypadku bledu
        // Dokłądniej mówiąc to nie parsuje żadnego urla w poście, jeśli przynajmniej jeden z nich ma (
        if (preg_last_error() === PREG_NO_ERROR) {
            return $processed;
        }

        return $text;
    }

    private function truncate(string $text): string
    {
        if (mb_strlen($text) < self::TITLE_LEN) {
            return $text;
        }

        if ($this->host === parse_url($text, PHP_URL_HOST)) {
            return $text;
        }

        $padding = (self::TITLE_LEN - mb_strlen(self::TITLE_DOTS)) / 2;

        $result = mb_substr($text, 0, $padding);
        $result .= self::TITLE_DOTS;
        $result .= mb_substr($text, -$padding);

        return $result;
    }
}

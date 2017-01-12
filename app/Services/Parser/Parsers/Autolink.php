<?php

namespace Coyote\Services\Parser\Parsers;

class Autolink extends Parser implements ParserInterface
{
    // http://daringfireball.net/2010/07/improved_regex_for_matching_urls
    const REGEXP_URL = '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))#u';
    const REGEXP_EMAIL = '#(^|[\n \[\]\:<>&;]|\()([a-z0-9&\-_.]+?@[\w\-]+\.(?:[\w\-\.]+\.)?[\w]+)#i';

    const TITLE_LEN = 64;
    const TITLE_DOTS = '[...]';

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        $text = $this->hashBlock($text, ['code', 'a']);
        $text = $this->hashInline($text, 'img');

        $text = $this->parseUrl($text);
        $text = $this->parseEmail($text);

        $text = $this->unhash($text);

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    private function parseUrl(string $text): string
    {
        $processed = preg_replace_callback(
            self::REGEXP_URL,
            function ($match) {
                $url = $match[0];

                if (!preg_match('#^[\w]+?://.*?#i', $url)) {
                    $url = 'http://' . $url;
                }

                $title = $this->truncate(htmlspecialchars($match[0], ENT_QUOTES, 'UTF-8', false));

                return link_to($url, $title);
            },
            $text
        );

        // regexp posiada buga, nie parsuje poprawnie URL-i jezeli zawiera on (
        // poki co nie mam rozwiazania na ten problem, dlatego zwracamy nieprzeprasowany tekst
        // w przypadku bledu
        if (preg_last_error() === PREG_NO_ERROR) {
            return $processed;
        }

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    private function parseEmail(string $text): string
    {
        return preg_replace(self::REGEXP_EMAIL, "\$1<a href=\"mailto:\$2\">$2</a>", $text);
    }

    /**
     * @param string $text
     * @param int $length
     * @param string $dots
     * @return string
     */
    private function truncate(string $text, int $length = self::TITLE_LEN, string $dots = self::TITLE_DOTS): string
    {
        if (mb_strlen($text) > $length) {
            $padding = ($length - mb_strlen($dots)) / 2;

            $result = mb_substr($text, 0, $padding);
            $result .= $dots;
            $result .= mb_substr($text, -$padding);

            return $result;
        }

        return $text;
    }
}

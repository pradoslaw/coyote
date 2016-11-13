<?php

namespace Coyote\Services\Parser\Helpers;

class Hash
{
    /**
     * @param string $html
     * @return array
     */
    public function grab($html)
    {
        if (!$html) {
            return [];
        }

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

        // ignore html errors
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument;
        $dom->loadHTML($html);

        $links = $dom->getElementsByTagName('a');
        $hash = [];

        foreach ($links as $link) {
            if (strlen($link->nodeValue) > 0 && '#' === $link->nodeValue[0]
                && preg_match('~#([\p{L}\p{Mn}0-9\._+-]+)~u', $link->nodeValue, $match)) {
                $hash[] = mb_strtolower($match[1]);
            }
        }

        return array_unique($hash);
    }
}

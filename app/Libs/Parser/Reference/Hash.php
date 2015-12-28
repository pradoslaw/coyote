<?php

namespace Coyote\Parser\Reference;

class Hash
{
    /**
     * @param $html
     * @return array
     */
    public function grab($html)
    {
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

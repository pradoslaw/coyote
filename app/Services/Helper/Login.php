<?php

namespace Coyote\Services\Helper;

class Login
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
        // ignore html errors
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument;
        $dom->loadHTML($html);

        $links = $dom->getElementsByTagName('a');
        $usersId = [];

        foreach ($links as $link) {
            if (strlen($link->nodeValue) > 0 && '@' === $link->nodeValue[0]
                && preg_match('~.*/Profile/([0-9]+)~', $link->getAttribute('href'), $match)) {
                $usersId[] = $match[1];
            }
        }

        return array_unique($usersId);
    }
}

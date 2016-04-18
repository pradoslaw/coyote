<?php

namespace Coyote\Services\Parser\Reference;

class Link
{
    public function filter($html)
    {
        $links = [];

        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        if (preg_match_all("/$regexp/siU", $html, $matches, PREG_SET_ORDER)) {
            $links[] = $matches[2];
        }

        return array_unique($links);
    }
}

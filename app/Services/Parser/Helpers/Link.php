<?php

namespace Coyote\Services\Parser\Helpers;

class Link
{
    /**
     * @param string $html
     * @return array
     */
    public function filter($html)
    {
        $links = [];

        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";

        if (preg_match_all("/$regexp/siU", $html, $matches, PREG_SET_ORDER)) {
            for ($i = 0, $count = count($matches); $i < $count; $i++) {
                $links[] = $matches[$i][2];
            }
        }

        return array_unique($links);
    }
}

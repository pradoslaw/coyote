<?php

namespace Coyote\Services\Parser\Helpers;

use TRegx\CleanRegex\Pattern;

class Link
{
    public function filter(string $html): array
    {
        $regexp = '<a\s[^>]*href=("??)([^" >]*?)\1[^>]*>(.*)<\/a>';

        $links = Pattern::of($regexp, 'siU')->match($html)->group(2)->all();

        return array_unique($links);
    }
}

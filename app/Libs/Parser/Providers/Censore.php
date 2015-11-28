<?php

namespace Coyote\Parser\Providers;

class Censore implements ProviderInterface
{
    public function parse($text)
    {
        return $text;
    }
}

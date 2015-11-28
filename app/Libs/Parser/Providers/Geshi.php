<?php

namespace Coyote\Parser\Providers;

class Geshi implements ProviderInterface
{
    public function parse($text)
    {
        return $text;
    }
}

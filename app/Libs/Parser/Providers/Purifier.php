<?php

namespace Coyote\Parser\Providers;

/**
 * Class Purifier
 * @package Coyote\Parser\Providers
 */
class Purifier implements ProviderInterface
{
    public function parse($text)
    {
        return \Purifier::clean($text);
    }
}

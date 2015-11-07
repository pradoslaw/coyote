<?php

namespace Coyote\Parser\Providers;

/**
 * Class Markdown
 * @package Coyote\Parser\Providers
 */
class Markdown implements ProviderInterface
{
    public function parse($text)
    {
        return \ParsedownExtra::instance()->setBreaksEnabled(true)->text($text);
    }
}

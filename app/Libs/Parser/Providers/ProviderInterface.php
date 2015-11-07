<?php

namespace Coyote\Parser\Providers;

/**
 * Interface ProviderInterface
 * @package Coyote\Parser\Providers
 */
interface ProviderInterface
{
    /**
     * @param string $text
     * @return mixed
     */
    public function parse($text);
}

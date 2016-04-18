<?php

namespace Coyote\Services\Parser\Providers;

/**
 * Interface ProviderInterface
 */
interface ProviderInterface
{
    /**
     * @param string $text
     * @return mixed
     */
    public function parse($text);
}

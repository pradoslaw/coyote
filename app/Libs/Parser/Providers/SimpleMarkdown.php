<?php

namespace Coyote\Parser\Providers;

/**
 * Uproszczony Markdown, np. dla komentarzy na forum czy stopek w postach gdzie nie mozemy sobie pozwolic
 * na obsluge pelnego markdowna
 *
 * Class SimpleMarkdown
 * @package Coyote\Parser\Providers
 */
class SimpleMarkdown extends \Parsedown implements ProviderInterface
{
    public function parse($text)
    {
        //
    }
}

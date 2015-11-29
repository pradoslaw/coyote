<?php

namespace Coyote\Parser\Providers;

/**
 * Uproszczony Markdown, np. dla komentarzy na forum czy stopek w postach gdzie nie mozemy sobie pozwolic
 * na obsluge pelnego markdowna
 *
 * Class SimpleMarkdown
 * @package Coyote\Parser\Providers
 */
class SimpleMarkdown extends Markdown implements ProviderInterface
{
    protected function blockHeader($line)
    {
        return null;
    }

    protected function blockCode($line, $block = null)
    {
        return null;
    }

    protected function blockCodeContinue($line, $block)
    {
        return null;
    }

    protected function blockCodeComplete($block)
    {
        return null;
    }

    protected function blockComment($line)
    {
        return null;
    }

    protected function blockFencedCode($line)
    {
        return null;
    }

    protected function blockList($line)
    {
        return null;
    }

    protected function blockQuote($line)
    {
        return null;
    }

    protected function blockRule($line)
    {
        return null;
    }

    protected function blockSetextHeader($line, array $block = null)
    {
        return null;
    }

    protected function blockReference($line)
    {
        return null;
    }

    protected function blockTable($line, array $block = null)
    {
        return null;
    }

    protected function inlineImage($excerpt)
    {
        return null;
    }

    protected function inlineStrikethrough($excerpt)
    {
        return null;
    }

    /**
     * @param string $text
     * @return mixed|string
     */
    public function parse($text)
    {
        return $this->line($text);
    }
}

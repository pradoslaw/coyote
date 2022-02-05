<?php

namespace Coyote\Services\Parser\Parsers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Uproszczony Markdown, np. dla komentarzy na forum czy stopek w postach gdzie nie mozemy sobie pozwolic
 * na obsluge pelnego markdowna
 *
 * Class SimpleMarkdown
 */
class SimpleMarkdown extends Markdown
{
    public function parse(string $text): string
    {
        $environment = new Environment($this->defaultConfig());
        $environment->addExtension(new InlinesOnlyExtension());
        $environment->addExtension(new MentionExtension());

        $converter = new MarkdownConverter($environment);

        return (string) $converter->convert($text);
    }
}

<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Parser\Extensions\InternalLinkExtension;
use Coyote\Services\Parser\WikiLinksInlineParser;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Uproszczony Markdown, np. dla komentarzy na forum czy stopek w postach gdzie nie mozemy sobie pozwolic
 * na obsluge pelnego markdowna
 */
class SimpleMarkdown extends Markdown
{
    private array $config;

    public function __construct(
        UserRepository $user,
        PageRepository $page,
        string         $host,
        bool           $singleLine)
    {
        parent::__construct($user, $page, $host);
        if ($singleLine) {
            $this->config = ['renderer' => ['soft_break' => "\n"]];
        } else {
            $this->config = ['renderer' => ['soft_break' => "<br>\n"]];
        }
    }

    public function parse(string $text): string
    {
        $environment = new Environment(\array_merge($this->defaultConfig(), $this->config));
        $environment->addExtension(new InlinesOnlyExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new MentionExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new InternalLinkExtension($this->page));
        $environment->addInlineParser(new WikiLinksInlineParser($this->page), 100);
        $converter = new MarkdownConverter($environment);
        return $converter->convert($text);
    }
}

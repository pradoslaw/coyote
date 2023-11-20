<?php
namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Parser\Extensions\InternalLinkExtension;
use Coyote\Services\Parser\Extensions\MentionExtension;
use Coyote\Services\Parser\WikiLinksInlineParser;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\MarkdownConverter;

class SimpleMarkdown extends Markdown
{
    private bool $singleLine;

    public function __construct(
        UserRepository $user,
        PageRepository $page,
        string         $host,
        bool           $singleLine)
    {
        parent::__construct($user, $page, $host);
        $this->singleLine = $singleLine;
    }

    public function parse(string $text): string
    {
        $environment = new Environment(['renderer' => [
            'soft_break' => $this->singleLine ? "\n" : "<br>\n",
        ]]);
        $environment->addExtension(new InlinesOnlyExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new MentionExtension($this->user));
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new InternalLinkExtension($this->page, $this->host));
        $environment->addInlineParser(new WikiLinksInlineParser($this->page), 100);
        $converter = new MarkdownConverter($environment);
        return $converter->convert($text);
    }
}

<?php
namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Parser\Extensions\EmojiNode;
use Coyote\Services\Parser\Extensions\EmojiParser;
use Coyote\Services\Parser\Extensions\EmojiRenderer;
use Coyote\Services\Parser\Extensions\InternalLinkExtension;
use Coyote\Services\Parser\Extensions\MentionExtension;
use Coyote\Services\Parser\Extensions\YoutubeLinkExtension;
use Coyote\Services\Parser\WikiLinksInlineParser;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;

class Markdown implements Parser
{
    public function __construct(
        protected UserRepository $user,
        protected PageRepository $page,
        protected string         $host) {}

    public function parse(string $text): string
    {
        $environment = new Environment(['renderer' => ['soft_break' => "<br>\n"]]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());
        $environment->addExtension(new MentionExtension($this->user));
        $environment->addExtension(new InternalLinkExtension($this->page, $this->host));
        $environment->addInlineParser(new WikiLinksInlineParser($this->page), 100);
        $environment->addExtension(new YoutubeLinkExtension());
        $environment->addInlineParser(new EmojiParser());
        $environment->addRenderer(EmojiNode::class, new EmojiRenderer());
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer());

        $converter = new MarkdownConverter($environment);
        return $converter->convert($text);
    }
}

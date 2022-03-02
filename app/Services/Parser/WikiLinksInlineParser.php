<?php

namespace Coyote\Services\Parser;

use Coyote\Page;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

class WikiLinksInlineParser implements InlineParserInterface
{
    public function __construct(private PageRepository $page)
    {
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex('\[\[(.*?)(\|(.*?))*\]\]');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        // This seems to be a valid match
        // Advance the cursor to the end of the match
        $cursor->advanceBy($inlineContext->getFullMatchLength());

        // Grab the Twitter handle
        $match = $inlineContext->getSubMatches();

        $link = $this->createLink(...$match);

        $inlineContext->getContainer()->appendChild($link);

        return true;
    }

    private function createLink(string $path, string $title = ''): Link
    {
        $title = ltrim($title, '|');
        $path = trim($path, '/?&[');

        $pathSlug = '/' . str_replace(' ', '_', $path);
        $hash = $this->getHashFromPath($pathSlug);

        $page = $this->getPath($pathSlug);

        if ($page) {
            $link = new Link($page->path . ($hash ? '#' . $hash : ''), $title ?: $page->title);
        } else {

            $link = new Link('Create' . $pathSlug, $title ?: $this->getTitleFromPath($path), 'Dokument nie istnieje');
            $link->data->set('attributes/class', 'link-broken');
        }

        return $link;
    }

    private function getPath(string $path): ?Page
    {
        return $this->page->findByPath($path);
    }

    /**
     * @param string $path
     * @return string
     */
    private function getHashFromPath(string &$path): string
    {
        $hash = '';

        if (($pos = strpos($path, '#')) !== false) {
            $hash = htmlspecialchars(substr($path, $pos + 1));
            $path = substr($path, 0, $pos);
        }

        return $hash;
    }

    private function getTitleFromPath(string $path): string
    {
        return str_replace('_', ' ', array_last(explode('/', $path)));
    }
}

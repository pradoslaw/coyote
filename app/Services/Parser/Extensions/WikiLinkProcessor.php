<?php

namespace Coyote\Services\Parser\Extensions;

use Coyote\Page;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Inline\AbstractStringContainer;

class WikiLinkProcessor implements DelimiterProcessorInterface
{
    public function __construct(private PageRepository $page)
    {
    }

    public function getOpeningCharacter(): string
    {
        return '[';
    }

    public function getClosingCharacter(): string
    {
        return ']';
    }

    public function getMinLength(): int
    {
        return 2;
    }

    public function getDelimiterUse(DelimiterInterface $opener, DelimiterInterface $closer): int
    {
        return 2;
    }

    public function process(AbstractStringContainer $opener, AbstractStringContainer $closer, int $delimiterUse): void
    {
        $next = $opener->next();
        $innerText = $next->getLiteral();

        $link = $this->createLink($innerText);

        $opener->next()->replaceWith($link);
    }

    private function createLink(string $innerText): Link
    {
        $path = $innerText;
        $title = '';

        if (str_contains($innerText, '|')) {
            [$path, $title] = explode('|', $innerText);
        }

        $path = '/' . str_replace(' ', '_', trim($path, '/?&['));
        $hash = $this->getHashFromPath($path);

        $page = $this->getPath($path);

        if ($page) {
            $link = new Link($page->path . ($hash ? '#' . $hash : ''), $title ?: $page->title);
        } else {
            $link = new Link('Create' . $path, $title, 'Dokument nie istnieje');
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
}

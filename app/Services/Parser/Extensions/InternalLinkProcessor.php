<?php
namespace Coyote\Services\Parser\Extensions;

use Coyote\Domain\Url;
use Coyote\Page;
use Coyote\Post;
use Coyote\Repositories\Eloquent\PageRepository;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExternalLink\ExternalLinkProcessor;
use League\CommonMark\Node\Inline\Text;

readonly class InternalLinkProcessor
{
    public function __construct(private PageRepository $page, private string $host)
    {
    }

    public function __invoke(DocumentParsedEvent $event): void
    {
        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof Link) {
                $this->setLinkNameOfPageTitle($node);
            }
        }
    }

    private function setLinkNameOfPageTitle(Link $link): void
    {
        $url = new Url($link->getUrl());
        if ($url->malformed || $url->path === null) {
            return;
        }
        if ($url->host === null) {
            $path = $url->path;
            $this->resolveShortPostLink($path, $link);
        } else {
            $this->setInternalLinkTitle($link, $url->host, $url->path);
        }
    }

    private function resolveShortPostLink(mixed $path, Link $link): void
    {
        $postId = $this->shortLinkPostId($path);
        if ($postId) {
            $shortLink = $this->postCanonicalLink($postId);
            if ($shortLink) {
                $link->setUrl($shortLink);
            }
        }
    }

    private function shortLinkPostId(string $path): ?int
    {
        if (\preg_match('#^/Forum/([0-9]+)$#', $path, $match)) {
            return $match[1];
        }
        return null;
    }

    private function postCanonicalLink(int $id): ?string
    {
        /** @var Post $post */
        $post = Post::query()->find($id);
        if ($post) {
            return $this->canonicalLink($post);
        }
        return null;
    }

    private function canonicalLink(Post $post): string
    {
        $url = route('forum.topic', [$post->forum->slug, $post->topic->id, $post->topic->slug], absolute:false);
        return "{$url}?p={$post->id}#id{$post->id}";
    }

    private function setInternalLinkTitle(Link $link, string $host, string $path): void
    {
        if (\trim($path, '/') === '') {
            return;
        }
        if ($this->hasLabel($link)) {
            return;
        }
        if (ExternalLinkProcessor::hostMatches($host, $this->host)) {
            $page = $this->pageByPath(\urlDecode($path));
            if ($page) {
                $this->setTitle($link, $page->title);
            }
        }
    }

    private function hasLabel(Link $link): bool
    {
        $child = $link->firstChild();
        if ($child instanceof Text) {
            return $child->getLiteral() !== $link->getUrl();
        }
        return false;
    }

    private function pageByPath(string $path): ?Page
    {
        foreach (['/Profile', '/User'] as $excludePath) {
            if (\str_starts_with($path, $excludePath)) {
                return null;
            }
        }
        return $this->page->findByPath($path);
    }

    private function setTitle(Link $link, string $title): void
    {
        $link->setTitle($title);
        $lastChild = $link->lastChild();
        if ($lastChild instanceof Text) {
            $lastChild->setLiteral($title);
        }
    }
}

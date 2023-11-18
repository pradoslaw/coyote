<?php

namespace Coyote\Services\Parser\Extensions;

use Coyote\Page;
use Coyote\Repositories\Eloquent\PageRepository;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExternalLink\ExternalLinkProcessor;
use League\CommonMark\Node\Inline\Text;
use League\Config\ConfigurationInterface;

class InternalLinkProcessor
{
    use LinkSupport;

    public function __construct(
        private PageRepository         $page,
        private ConfigurationInterface $config)
    {
    }

    public function __invoke(DocumentParsedEvent $event): void
    {
        $internalHosts = $this->config->get('internal_link/internal_hosts');
        $document = $event->getDocument();
        foreach ($document->iterator() as $node) {
            if ($node instanceof Link) {
                $this->setLinkNameOfPageTitle($node, $internalHosts);
            }
        }
    }

    private function setLinkNameOfPageTitle(Link $link, mixed $internalHosts): void
    {
        $components = parse_url($link->getUrl());
        if (!$this->isValidLink($components) || $this->linkHasLabel($link)) {
            return;
        }
        if (!ExternalLinkProcessor::hostMatches($components['host'], $internalHosts)) {
            return;
        }
        $page = $this->pageByPath(urldecode($components['path']));
        if (!$page) {
            return;
        }
        $link->setTitle($page->title);
        $lastChild = $link->lastChild();
        if ($lastChild instanceof Text) {
            $lastChild->setLiteral($page->title);
        }
    }

    protected function pageByPath(string $path): ?Page
    {
        foreach (['/Profile', '/User'] as $excludePath) {
            if (str_starts_with($path, $excludePath)) {
                return null;
            }
        }
        return $this->page->findByPath($path);
    }
}

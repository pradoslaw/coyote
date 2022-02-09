<?php

namespace Coyote\Services\Parser\Extensions;

use Coyote\Page;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExternalLink\ExternalLinkProcessor;
use League\Config\ConfigurationInterface;

class InternalLinkProcessor
{
    use LinkSupport;

    private const EXCLUDE_PATHS = ['/Profile', '/User'];

    public function __construct(private PageRepository $page, private ConfigurationInterface $config)
    {
    }

    public function __invoke(DocumentParsedEvent $e): void
    {
        $internalHosts = $this->config->get('internal_link/internal_hosts');

        foreach ($e->getDocument()->iterator() as $link) {
            if (!($link instanceof Link)) {
                continue;
            }

            $components = parse_url($link->getUrl());

            // link is invalid. something went wrong
            if (!$this->isValidLink($components) || $this->linkHasLabel($link)) {
                continue;
            }

            if (ExternalLinkProcessor::hostMatches($components['host'], $internalHosts) && $page = $this->realTitle(urldecode($components['path']))) {
                $link->setTitle($page->title);
                $link->lastChild()->setLiteral($page->title);
            }
        }
    }

    protected function realTitle(string $path): ?Page
    {
        foreach (self::EXCLUDE_PATHS as $excludePath) {
            if (str_starts_with($path, $excludePath)) {
                return null;
            }
        }

        return $this->page->findByPath($path);
    }
}

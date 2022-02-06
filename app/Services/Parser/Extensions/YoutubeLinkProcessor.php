<?php

namespace Coyote\Services\Parser\Extensions;

use Coyote\Services\Parser\Iframe;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExternalLink\ExternalLinkProcessor;

class YoutubeLinkProcessor
{
    private const YOUTUBE_HOSTS = ['youtube.com', 'youtu.be', 'www.youtube.com', 'www.youtu.be'];

    public function __invoke(DocumentParsedEvent $e): void
    {
        foreach ($e->getDocument()->iterator() as $link) {
            if (! ($link instanceof Link)) {
                continue;
            }

            $components = parse_url($link->getUrl());
            $path = trim($components['path'] ?? '', '/');

            if (!$this->isYoutubeLink($components['host'], $path) || $link->firstChild()->getLiteral() !== $link->getUrl()) {
                continue;
            }

            $iframe = $path === 'watch'
                ? $this->makeIframeFromFullPath($components)
                : $this->makeIframeFromShortPath($components);

            if ($iframe) {
                $link->replaceWith($iframe);
            }
        }
    }

    private function isYoutubeLink(string $host, string $path): bool
    {
        return !empty($path) && ExternalLinkProcessor::hostMatches($host, self::YOUTUBE_HOSTS);
    }

    private function makeIframeFromFullPath(array $components): ?Iframe
    {
        parse_str($components['query'] ?? '', $query);

        if (empty($query['v'])) {
            return null;
        }

        parse_str($components['fragment'] ?? '', $hash);

        return $this->makeIframe($query['v'], $this->timeToSeconds($hash['t'] ?? null));
    }

    private function makeIframeFromShortPath(array $components): ?Iframe
    {
        parse_str($components['query'] ?? '', $query);

        return $this->makeIframe(trim($components['path'], '/'), $this->timeToSeconds($query['t'] ?? null));
    }

    private function makeIframe(string $videoId, string $start = null): Iframe
    {
        $iframe = new Iframe();
        $iframe->data->set('src', 'https://youtube.com/embed/' . $videoId . ($start !== null ? "?start=$start" : ''));
        $iframe->data->set('class', 'embed-responsive-item');
        $iframe->data->set('allowfullscreen', 'allowfullscreen');

        return $iframe;
    }

    private function timeToSeconds(?string $time): ?int
    {
        if (!$time) {
            return null;
        }

        if (preg_match('/(\d+)m(\d+)s/', $time, $match)) {
            return ($match[1] * 60) + $match[2];
        }

        return $time;
    }
}

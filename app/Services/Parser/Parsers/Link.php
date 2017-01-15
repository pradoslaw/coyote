<?php

namespace Coyote\Services\Parser\Parsers;

use Collective\Html\HtmlBuilder;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;

class Link extends Parser implements ParserInterface
{
    const LINK_TAG_REGEXP = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    const LINK_INTERNAL_REGEXP = '\[\[(.*?)(\|(.*?))*\]\]';

    /**
     * @var PageRepository
     */
    private $page;

    /**
     * @var string
     */
    private $host;

    /**
     * @var HtmlBuilder|null
     */
    private $html;

    /**
     * Link constructor.
     *
     * @param PageRepository $page
     * @param string $host
     * @param HtmlBuilder|null $html
     */
    public function __construct(PageRepository $page, string $host, HtmlBuilder $html = null)
    {
        $this->page = $page;
        $this->host = $host;
        $this->html = $html;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        $text = $this->hashBlock($text, 'code');
        $text = $this->hashInline($text, 'img');

        $text = $this->parseLinks($text);
        $text = $this->parseInternalAccessors($text);

        $text = $this->unhash($text);

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function parseLinks($text)
    {
        if (!preg_match_all('/' . self::LINK_TAG_REGEXP . '/siU', $text, $matches, PREG_SET_ORDER)) {
            return $text;
        }

        for ($i = 0, $count = count($matches); $i < $count; $i++) {
            $link  = $matches[$i][2];
            $title = $matches[$i][3];
            $match = $matches[$i][0];

            $text = $this->parseInternalLink($text, $match, $link, $title);
            $text = $this->parseYoutubeLinks($text, $match, $link);
        }

        return $text;
    }

    /**
     * @param string $text
     * @param string $match
     * @param string $url
     * @param string $title
     * @return string
     */
    protected function parseInternalLink($text, $match, $url, $title)
    {
        if (urldecode($title) === urldecode($url) && ($path = $this->getPathFromInternalUrl($url)) !== false) {
            $page = $this->page->findByPath($path);

            if ($page) {
                $text = str_replace($match, link_to($url, $page->title), $text);
            }
        }

        return $text;
    }

    /**
     * Parse "old" coyote links like [[Foo/Bar]] to http://4programmers.net/Foo/Bar
     *
     * @param $text
     * @return string
     */
    protected function parseInternalAccessors($text)
    {
        $text = $this->hashBlock($text, 'a');

        if (!preg_match_all('/' . self::LINK_INTERNAL_REGEXP . '/i', $text, $matches, PREG_SET_ORDER)) {
            return $text;
        }

        for ($i = 0, $count = count($matches); $i < $count; $i++) {
            $origin = $matches[$i][0];

            $path = '/' . str_replace(' ', '_', trim($matches[$i][1], '/?&'));

            $title = $matches[$i][3] ?? null;
            $hash = $this->getHashFromPath($path);

            $page = $this->page->findByPath($path);
            $attr = [];

            if (empty($page)) {
                $attr = ['class' => 'link-broken', 'title' => 'Dokument nie istnieje'];
                $path = 'Create' . $path;

                if (empty($title)) {
                    $title = str_replace('_', ' ', last(explode('/', $path)));
                }
            } else {
                $path = $page->path;
                $title = $title ?: $page->title;
            }

            $text = str_replace($origin, link_to($path . ($hash ? '#' . $hash : ''), $title, $attr), $text);
        }

        $text = $this->unhash($text);

        return $text;
    }

    /**
     * @param string $text
     * @param string $match
     * @param string $url
     * @return string
     */
    protected function parseYoutubeLinks($text, $match, $url)
    {
        if ($this->html === null) {
            return $text;
        }

        $components = parse_url($url);

        if ($this->getHost($components['host']) === 'youtube.com' && trim($components['path'], '/') === 'watch') {
            parse_str($components['query'], $query);

            $text = str_replace($match, $this->makeIframe($query['v']), $text);
        }

        return $text;
    }

    /**
     * @param string $videoId
     * @return string
     */
    private function makeIframe(string $videoId): string
    {
        $iframe = (string) $this->html->tag('iframe', '', [
            'src'   => 'https://youtube.com/embed/' . $videoId,
            'class' => 'embed-responsive-item'
        ]);

        return (string) $this->html->tag('div', $iframe, ['class' => 'embed-responsive embed-responsive-16by9']);
    }

    /**
     * Get path from url only if it's internal link (false if it's NOT internal link)
     *
     * @example http://4programmers.net/Foo/Bar => /Foo/Bar
     * @param string $url
     * @return string|bool
     */
    private function getPathFromInternalUrl($url)
    {
        $component = parse_url($url);
        $path = false;

        if (!empty($component['path']) && !empty($component['host'])) {
            // sprawdzamy, czy mamy do czynienia z linkiem wewnetrznym
            if ($this->host === $this->getHost($component['host'])) {
                $path = urldecode($component['path']);
            }
        }

        return $path;
    }

    /**
     * @param $path
     * @return string
     */
    private function getHashFromPath(&$path)
    {
        $hash = '';

        if (($pos = strpos($path, '#')) !== false) {
            $hash = htmlspecialchars(substr($path, $pos + 1));
            $path = substr($path, 0, $pos);
        }

        return $hash;
    }

    /**
     * Get host without "www" at the beginning.
     *
     * @param string $host
     * @return string
     */
    private function getHost(string $host): string
    {
        $parts = explode('.', $host);

        if ($parts[0] === 'www') {
            array_shift($parts);
        }

        return implode('.', $parts);
    }
}

<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\PageRepositoryInterface as Page;

class Link extends Parser implements ParserInterface
{
    const LINK_TAG_REGEXP = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    const LINK_INTERNAL_REGEXP = '\[\[(.*?)(\|(.*?))*\]\]';

    /**
     * @var Page
     */
    private $page;

    /**
     * @var string
     */
    private $host;

    /**
     * Link constructor.
     *
     * @param Page $page
     * @param string $host
     */
    public function __construct(Page $page, string $host)
    {
        $this->page = $page;
        $this->host = $host;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        $text = $this->hashBlock($text, 'code');
        $text = $this->hashInline($text, 'img');

        $text = $this->parseInternalLinks($text);
        $text = $this->parseInternalAccessors($text);

        $text = $this->unhash($text);

        return $text;
    }

    /**
     * @param $text
     * @return mixed
     */
    protected function parseInternalLinks($text)
    {
        if (!preg_match_all('/' . self::LINK_TAG_REGEXP . '/siU', $text, $matches, PREG_SET_ORDER)) {
            return $text;
        }

        for ($i = 0, $count = count($matches); $i < $count; $i++) {
            $link = $matches[$i][2];
            $title = $matches[$i][3];

            if (urldecode($title) === urldecode($link) && ($path = $this->getPathFromUrl($link)) !== false) {
                $page = $this->page->findByPath($path);
                $html = $matches[$i][0];

                if ($page) {
                    $text = str_replace($html, link_to($link, $page->title), $text);
                }
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
     * Get path from url only if it's internal link (false if it's NOT internal link)
     *
     * @example http://4programmers.net/Foo/Bar => /Foo/Bar
     * @param string $url
     * @return string|bool
     */
    private function getPathFromUrl($url)
    {
        $component = parse_url($url);
        $path = false;

        if (!empty($component['path']) && !empty($component['host'])) {
            // host odnosnika (np. 4programmers.net lub forum.4programmers.net). Przeksztalcamy
            // ten url tak, aby uzyskac tylko domene wyzszego rzedu, czyli 4programmers.net
            $parts = explode('.', $component['host']);
            $host = implode('.', array_slice($parts, -2, 2));

            // sprawdzamy, czy mamy do czynienia z linkiem wewnetrznym
            if (stripos($this->host, $host) !== false) {
                if ($parts[0] == 'www') {
                    array_shift($parts);
                }

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
}

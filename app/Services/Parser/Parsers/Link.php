<?php

namespace Coyote\Services\Parser\Parsers;

use Collective\Html\HtmlBuilder;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Services\Parser\Parsers\Parentheses\ParenthesesParser;
use Coyote\Services\Parser\Parsers\Parentheses\SymmetricParenthesesChunks;
use TRegx\CleanRegex\Match\Details\Match;
use TRegx\SafeRegex\preg;

class Link extends Parser implements ParserInterface
{
    const LINK_TAG_REGEXP = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    const LINK_INTERNAL_REGEXP = '\[\[(.*?)(\|(.*?))*\]\]';

    const REGEXP_EMAIL = '#(^|[\n \[\]\:<>&;]|\()([a-z0-9&\-_.]+?@[\w\-]+\.(?:[\w\-\.]+\.)?[\w]+)#i';

    private const PARENTHESES_LEVEL = 3;

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

    /** @var UrlFormatter */
    private $urlParser;

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
        $this->urlParser = new UrlFormatter($host, app('html'), new ParenthesesParser(new SymmetricParenthesesChunks(), self::PARENTHESES_LEVEL));
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        // first, make <a> from plain URL's
        // -----------------------------------------
        $text = $this->hashBlock($text, ['code', 'a']);
        $text = $this->hashInline($text, 'img');

        $text = $this->parseUrl($text);
        $text = $this->parseEmail($text);

        $text = $this->unhash($text);
        // ------------------------------------------

        $text = $this->hashBlock($text, 'code');
        $text = $this->hashInline($text, 'img');

        // then, parse internal links and youtube video links
        // --------------------------------------------------
        $text = $this->parseLinks($text);

        $text = $this->hashBlock($text, 'a');

        // at last, parse coyote markup
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
        if (!preg::match_all('/' . self::LINK_TAG_REGEXP . '/siU', $text, $matches, PREG_SET_ORDER)) {
            return $text;
        }

        for ($i = 0, $count = count($matches); $i < $count; $i++) {
            $link = $matches[$i][2];
            $title = $matches[$i][3];
            $match = $matches[$i][0];

            $text = $this->parseInternalLink($text, $match, $link, $title);
            $text = $this->parseYoutubeLinks($text, $match, $link, $title);
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
     * @param string $text
     * @param string $match
     * @param string $url
     * @param string $title
     * @return string
     */
    protected function parseYoutubeLinks($text, $match, $url, $title)
    {
        if ($this->html === null) {
            return $text;
        }

        if (urldecode($title) !== urldecode($url)) {
            return $text;
        }

        $components = parse_url($url);

        if ($this->isUrl($components)) {
            // get host without "www"
            $host = $this->getHost($components['host']);
            $path = trim($components['path'], '/');

            if ($host === 'youtube.com' && $path === 'watch') {
                parse_str($components['query'], $query);

                if (!empty($query['v'])) {
                    parse_str($components['fragment'] ?? '', $fragments);

                    $text = str_replace(
                        $match,
                        $this->makeIframe($query['v'], $this->timeToSeconds($fragments['t'] ?? null)),
                        $text
                    );
                }
            }

            if ($host === 'youtu.be' && $path !== '') {
                parse_str($components['query'] ?? '', $query);

                $text = str_replace($match, $this->makeIframe($path, $this->timeToSeconds($query['t'] ?? null)), $text);
            }
        }

        return $text;
    }

    /**
     * Parse "old" coyote links like [[Foo/Bar]] to http://4programmers.net/Foo/Bar
     *
     * @param string $text
     * @return string
     */
    protected function parseInternalAccessors($text)
    {
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

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function parseUrl(string $text): string
    {
        return $this->urlParser->parse($text);
    }

    /**
     * @param string $text
     * @return string
     */
    protected function parseEmail(string $text): string
    {
        return preg_replace(self::REGEXP_EMAIL, "\$1<a href=\"mailto:\$2\">$2</a>", $text);
    }

    /**
     * @param string|null $time
     * @return null|string
     */
    private function timeToSeconds($time)
    {
        if (!$time) {
            return null;
        }

        return pattern("(\d+)m(\d+)s")->match($time)
            ->findFirst(function (Match $match) {
                return ($match->get(1) * 60) + $match->group(2)->toInt();
            })
            ->orElse($time);
    }

    /**
     * @param string $videoId
     * @param string $start
     * @return string
     */
    private function makeIframe(string $videoId, string $start = null): string
    {
        $iframe = (string) $this->html->tag('iframe', '', [
            'src'             => 'https://youtube.com/embed/' . $videoId . ($start !== null ? "?start=$start" : ''),
            'class'           => 'embed-responsive-item',
            'allowfullscreen' => 'allowfullscreen'
        ]);

        return (string) $this->html->tag('div', $iframe, ['class' => 'embed-responsive embed-responsive-16by9']);
    }

    /**
     * Get path from url only if it's internal link (false if it's NOT internal link)
     *
     * @example http://4programmers.net/Foo/Bar => /Foo/Bar
     * @param string $url
     * @return string|false
     */
    private function getPathFromInternalUrl($url)
    {
        $components = parse_url($url);
        $path = false;

        if ($this->isUrl($components)) {
            // sprawdzamy, czy mamy do czynienia z linkiem wewnetrznym
            if ($this->host === $this->getHost($components['host'])) {
                $path = urldecode($components['path']);
            }
        }

        return $path;
    }

    /**
     * @param array|false $components
     * @return bool
     */
    private function isUrl($components)
    {
        if (!is_array($components)) {
            return false;
        }

        return (!empty($components['path']) && !empty($components['host']));
    }

    /**
     * @param string $path
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

<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\PageRepositoryInterface as Page;

class Link implements ParserInterface
{
    const LINK_TAG_REGEXP = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";

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
     * @todo Parsowanie linkow "starego" coyote typu [[Foo]]
     */
    public function parse($text)
    {
        if (preg_match_all('/' . self::LINK_TAG_REGEXP . '/siU', $text, $matches, PREG_SET_ORDER)) {
            for ($i = 0; $i < count($matches); $i++) {
                $link = $matches[$i][2];
                $title = $matches[$i][3];

                if ($title === $link && ($path = $this->getPathFromUrl($link)) !== false) {
                    $page = $this->page->findByPath($path);
                    $html = $matches[$i][0];

                    if ($page) {
                        $text = str_replace($html, link_to($link, $page->title), $text);
                    }
                }
            }
        }

        return $text;
    }

    /**
     * @param string $url
     * @return string
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

                // jezeli forum na nowym coyote nie bedzie dzialalo pod subdomena, to nie potrzebujemy
                // pinizszego kodu
//                if (count($parts) > 2) {
//                    $path = ucfirst(array_shift($parts)) . '/' . $path;
//                }
            }
        }

        return $path;
    }
}

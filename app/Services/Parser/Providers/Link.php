<?php

namespace Coyote\Services\Parser\Providers;

use Coyote\Repositories\Contracts\PageRepositoryInterface as Page;
use Illuminate\Http\Request;

class Link implements ProviderInterface
{
    const LINK_TAG_REGEXP = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";

    /**
     * @var Page
     */
    private $page;

    /**
     * @var Request
     */
    private $request;

    /**
     * Link constructor.
     *
     * @param Page $page
     * @param Request $request
     */
    public function __construct(Page $page, Request $request)
    {
        $this->page = $page;
        $this->request = $request;
    }

    /**
     * @param string $text
     * @return mixed|string
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
            if (stripos($this->request->getHost(), $host) !== false) {
                if ($parts[0] == 'www') {
                    array_shift($parts);
                }

                $path = urldecode($component['path']);

                if (count($parts) > 2) {
                    $path = ucfirst(array_shift($parts)) . '/' . $path;
                }
            }
        }

        return $path;
    }
}

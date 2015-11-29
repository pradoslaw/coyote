<?php

namespace Coyote\Parser\Providers;

class Link implements ProviderInterface
{
    public function __construct()
    {

    }

    public function parse($text)
    {
//        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
//        if (preg_match_all("/$regexp/siU", $text, $matches, PREG_SET_ORDER)) {
//            $links[] = $matches[2];
//        }


        return $text;
    }

    /**
     * Wyszukuje adresow URL umieszczonych w tekscie
     *
     * @param $text string
     * @return array
     */
//    public function find($text)
//    {
//        $links = [];
//
//        $this->processInline($text, function ($line) use (&$links) {
//            $links = array_merge($links, $this->grabUrl($line));
//        });
//
//        return array_pluck($links, 'path');
//    }

    /**
     * Metoda ktora wyciaga adresy URL z pojedynczej linii tekstu
     * @param $line string
     * @return array
     */
//    private function grabUrl($line)
//    {
//        preg_match_all($this->urlPattern, $line, $matches, PREG_OFFSET_CAPTURE);
//        $urls = [];
//
//        foreach ($matches[0] as $match) {
//            list($url, ) = $match;
//
//            $length = strlen($url);
//            $offset = strlen(substr($line, 0, $match[1]));
//
//            if (substr($url, 0, 4) == 'www.') {
//                $url = 'http://' . $url;
//            }
//            $component = parse_url($url);
//
//            if (!empty($component['path']) && !empty($component['host'])) {
//                // host odnosnika (np. 4programmers.net lub forum.4programmers.net). Przeksztalcamy
//                // ten url tak, aby uzyskac tylko domene wyzszego rzedu, czyli 4programmers.net
//                $parts = explode('.', $component['host']);
//                $host = implode('.', array_slice($parts, -2, 2));
//
//                // sprawdzamy, czy mamy do czynienia z linkiem wewnetrznym
//                if (stripos($this->request->getHost(), $host) !== false) {
//                    if ($parts[0] == 'www') {
//                        array_shift($parts);
//                    }
//
//                    $path = urldecode(ltrim($component['path'], '/'));
//
//                    if (count($parts) > 2) {
//                        $path = ucfirst(array_shift($parts)) . '/' . $path;
//                    }
//                    $urls[] = ['path' => $path, 'url' => $url, 'offset' => $offset, 'length' => $length];
//                }
//            }
//        }
//
//        return $urls;
//    }
}

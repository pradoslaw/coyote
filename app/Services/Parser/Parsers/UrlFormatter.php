<?php

namespace Coyote\Services\Parser\Parsers;

use Collective\Html\HtmlBuilder;
use Coyote\Services\Parser\Parsers\Parentheses\ParenthesesParser;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class UrlFormatter
{
    // http://daringfireball.net/2010/07/improved_regex_for_matching_urls
    // modified - removed part with parsing of parentheses, because that's
    // done by ParenthesesParser
    private const REGEXP_URL = '\b(?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)[^\s<>]+[^\s`!(\[\]{};:\'".,<>?«»“”‘’]';
    private const TITLE_LEN = 64;

    /** @var string */
    private $host;
    /** @var HtmlBuilder */
    private $html;
    /** @var ParenthesesParser */
    private $parser;
    /** @var Pattern */
    private $urlPattern;

    public function __construct(string $host, HtmlBuilder $html, ParenthesesParser $parser)
    {
        $this->host = $host;
        $this->html = $html;
        $this->parser = $parser;
        $this->urlPattern = Pattern::of(self::REGEXP_URL, 'i');
    }

    public function parse(string $text): string
    {
        return $this->urlPattern->replace($text)->callback(fn(Detail $match) => $this->processLink($match->text()));
    }

    private function processLink(string $url): string
    {
        return join(array_map([$this, 'buildLink'], $this->parser->parse($url)));
    }

    private function buildLink(string $url): string
    {
        if ($this->urlPattern->test($url)) {
            return $this->html->link($this->prependSchema($url), $this->truncate($url));
        }

        return $url;
    }

    private function prependSchema(string $url): string
    {
        if (pattern('^[\w]+?://', 'i')->fails($url)) {
            return "http://$url";
        }

        return $url;
    }

    private function truncate(string $text): string
    {
        if (mb_strlen($text) < self::TITLE_LEN) {
            return $text;
        }

        if ($this->host === parse_url($text, PHP_URL_HOST)) {
            return $text;
        }

        return $this->truncateToLengthWith($text, self::TITLE_LEN, '[...]');
    }

    private function truncateToLengthWith(string $text, int $length, string $substitute): string
    {
        $padding = ($length - mb_strlen($substitute)) / 2;

        return mb_substr($text, 0, $padding) . $substitute . mb_substr($text, -$padding);
    }
}

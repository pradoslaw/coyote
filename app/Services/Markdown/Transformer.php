<?php

namespace Coyote\Services\Markdown;

use Coyote\Services\Parser\Parsers\Parser;

class Transformer extends Parser
{
    public function transform($text)
    {
        // na poczatek zmiana adresow, wszedzie, w calym tekscie
        $text = $this->url($text);
        // pozbywamy sie starych linkow, rowniez wszedzie
        $text = $this->links($text);

        // znaczniki <plain> nie maja racji bytu
        $text = $this->removePlain($text);
        // poprawa tagow <code>
        $text = $this->fixCodeTag($text);

        // @todo usunac znacznik <div> (tylko poza <code>!) albo zezwolic na jego korzystanie
        // @todo usunac z tekstu `<code="język"></code>` na \```php\```

        return $text;
    }

    private function url(string $text): string
    {
        $text = str_replace('forum.4programmers.net', '4programmers.net/Forum', $text);

        // to musi byc przed kolejna linia kodu
        $text = preg_replace('~4programmers\.net\/Forum\/viewtopic\.php\?p\=(\d+)\&~', '4programmers.net/Forum/$1?', $text);
        $text = preg_replace('~4programmers\.net\/Forum\/viewtopic\.php\?p\=(\d+)~', '4programmers.net/Forum/$1', $text);

        return $text;
    }

    private function links(string $text): string
    {
        $text = preg_replace(
            "#<url>([a-z]+?://)([][()^{}%$0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.,?!%*_\#:;~\\&$@/=+-]+)</url>#si",
            "<a href=\"$1$2\">$1$2</a>",
            $text
        );

        $text = preg_replace(
            "#<url=([a-z]+?://)([][()^{}%$0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.,?!%*_\#:;~\\&$@/=+-]+)>(.+)</url>#Usi",
            "<a href=\"$1$2\">$3</a>",
            $text
        );

        $text = preg_replace(
            "#<url=\"([a-z]+?://)([][()^{}%$0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.,?!%*_\#:;~\\&$@/=+-]+)\">(.+)</url>#Usi",
            "<a href=\"$1$2\">$3</a>",
            $text
        );

        $text = preg_replace("#<email>([a-z0-9\-_.]+?@[a-z0-9\-_.]+?)</email>#si", "$1$2", $text);

        $text = preg_replace(
            "#<image>([a-z]+?://)([a-z0-9\-\.,\?!%\*\[\]_\#:;~\\&$@\/=\+\^{}() ]+)</image>#si",
            "![user image]($1$2)",
            $text
        );

        $text = preg_replace(
            "#<wiki>([^<]*)</wiki>#si",
            "<a href=\"http://pl.wikipedia.org/wiki/$1\">$1</a>",
            $text
        );

        return $text;
    }

    private function removePlain($text)
    {
        return str_replace(['<plain>', '</plain>'], '', $text);
    }

    private function fixCodeTag($text)
    {
        $callable = function ($matches) {
            if (empty($matches[1])) {
                return '<code>';
            }

            return '<code class="' . str_replace('c++', 'cpp', strtolower($matches[1])) . '">';
        };

        $text = preg_replace_callback(
            '|<code(?:(?:=([a-z\d#-]+))?(?::((?:[a-z]+\|)*[a-z]+))?)?>|is',
            $callable,
            $text
        );

        $text = preg_replace_callback(
            '|<code="([a-z\+]+)">|is',
            $callable,
            $text
        );

        $syntaxTags = 'php|delphi|cpp|asm';

        /* zastapienie starych znacznikow kolorowania skladni - nowymi */
        $text = preg_replace("#<({$syntaxTags}*)>(.*?)</({$syntaxTags}*)>#is", '<code class="$1">$2</code>', $text);

        return $text;
    }
}

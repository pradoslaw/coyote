<?php

namespace Coyote\Services\Markdown;

use Coyote\Services\Parser\Parsers\Parser;

function mb_substr_replace($string, $replacement, $start, $length = 0)
{
    $before = mb_substr($string, 0, $start, "UTF-8");
    $after = mb_substr($string, $start + $length, mb_strlen($string), "UTF-8");

    return $before . $replacement . $after;
}

class Transformer extends Parser
{
    public $mapping = [];
    public $quote = [];

    public function transform($text)
    {
        // na poczatek zmiana adresow, wszedzie, w calym tekscie
        $text = $this->url($text);
        // pozbywamy sie starych linkow, rowniez wszedzie
        $text = $this->links($text);
        // zastapienie {{Image}} oraz {{File}}. teraz juz tego nie uzywamy
        $text = $this->inlineImages($text);

        // w linkach nic nie zmieniamy
        $text = $this->hashBlock($text, ['a', 'img']);

        // znaczniki <plain> nie maja racji bytu. zamieniamy <plain> na <code>
        $text = $this->removePlain($text);
        // poprawa tagow <code> (dodanie atrybutu class)
        $text = $this->fixCodeTag($text);

        // w <code> nie beda dokonywane dalsze konwersje
        $text = $this->hashBlock($text, 'code');

        $text = $this->fixDoubleApostrophes($text);
        // usuniecie backtick z tekstu
        $text = $this->hashBacktick($text);

        $text = $this->removeTtTag($text);
        $text = $this->hashBacktick($text);

        $text = $this->makeList($text);
        $text = $this->style($text);
        $text = $this->headline($text);
        $text = $this->quote($text);

        // @todo usunac z tekstu `<code="język"></code>` na \```jezyl\```

        $text = $this->unhash($text);

        // zamianiana <code> na markdown
        $text = $this->removeCodeTag($text);

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

    private function inlineImages(string $text): string
    {
        preg_match_all("#{{(Image|File):(.*?)(\|(.*))*}}#i", $text, $matches);
        if (!$matches[0]) {
            return $text;
        }

        for ($i = 0, $limit = sizeof($matches[0]); $i < $limit; $i++) {
            $name = $file = $matches[2][$i];

            if (isset($this->mapping[$name])) {
                $file = $this->mapping[$name];
            }

            if ($matches[1][$i] === 'Image') {
                @list(, $width) = explode('|', $matches[4][$i]);
                if ($width) {
                    $pathinfo = pathinfo($file);
                    $file = $pathinfo['filename'] . '-image(' . $width . 'x' . $width . ').' . $pathinfo['extension'];
                }

                $replacement = '![' . $name . '](//cdn.4programmers.net/uploads/attachment/' . $file . ')';
            } else {
                $replacement = '<a href="//cdn.4programmers.net/uploads/attachment/' . $file . '">' . $name . '</a>';
            }

            $text = str_replace($matches[0][$i], $replacement, $text);
        }

        return $text;
    }

    private function removePlain($text)
    {
        // <plain> zamieniamy na <code>. stare znaczniki (np. '') ktore znajduja sie w <plain>, nie beda
        // zamienione na markdown
        return str_replace(['<plain>`</plain>', "<plain>''</plain>", '<plain>', '</plain>'], ['\`', '\`', '<code>', '</code>'], $text);
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

    private function fixDoubleApostrophes(string $text): string
    {
        $lines = $this->splitLineBreaks($text);

        foreach ($lines as &$line) {
            $searchFor = "''";

            while (($start = mb_strpos($line, $searchFor)) !== false) {
                $end = mb_strpos($line, $searchFor, $start + 1);

                if ($end === false) {
                    break;
                } else {
                    $line = mb_substr_replace($line, '`', $start, 2);
                    $line = mb_substr_replace($line, '`', $end - 1, 2);
                }
            }

//            $line = str_replace(["`''`", "''`", '``'], ['`', '`', '`'], $line);
        }

        return $this->joinLineBreaks($lines);
    }

    private function removeTtTag(string $text): string
    {
        $text = $this->replaceTagWithBacktick($text, 'tt');
        $text = $this->replaceTagWithBacktick($text, 'kbd');

        return $text;
    }

    private function replaceTagWithBacktick(string $text, string $tag): string
    {
        $lines = $this->splitLineBreaks($text);

        foreach ($lines as &$line) {
            while (($start = mb_strpos($line, "<$tag>")) !== false) {
                $end = mb_strpos($line, "</$tag>", $start + 1);

                if ($end === false) {
                    break;
                } else {
                    $len = strlen("<$tag>");

                    $line = mb_substr_replace($line, '`', $start, $len);
                    $line = mb_substr_replace($line, '`', $end - $len + 1, strlen("</$tag>"));
                }
            }
        }

        return $this->joinLineBreaks($lines);
    }

    private function makeList(string $text): string
    {
        $lines = $this->splitLineBreaks($text);
        $count = [];

        $start = false;

        foreach ($lines as $index => &$line) {
            $char = isset($line{0}) ? $line{0} : '';
            $indent = strspn($line, '#*');

            if ($char == '#' || $char == '*') {
                if (!isset($line[$indent]) || $line[$indent] !== ' ') {
                    continue;
                }

                if ($indent > 1 && $char == '*') {
                    $line = mb_substr_replace($line, str_repeat(' ', $indent - 1) . $char, 0, $indent);
                }

                if ($char == '#') {
                    if ($start == false) {
                        if (!isset($lines[$index + 1]) || $lines[$index + 1][0] != '#') {
                            continue;
                        }
                    }

                    unset($count[$indent + 1]);

                    if (!isset($count[$indent])) {
                        $count[$indent] = 0;
                    }

                    $line = mb_substr_replace($line, str_repeat(' ', $indent - 1) . ++$count[$indent] . '.', 0, $indent);
                }

                $start = true;
            } else {
                $count = [];
                $start = false;
            }
        }

        return $this->joinLineBreaks($lines);
    }

    const ITALIC = '//';
    const SUB = ',,';
    const SUP = '^';

    private function style(string $text): string
    {
        $lines = $this->splitLineBreaks($text);

        foreach ($lines as &$line) {
            $arr = preg_split("/( |http:|www\.|\/\/|,,|\^+)/", $line, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            if (isset($arr[1])) {
                $replacement = [self::ITALIC => '*', self::SUB => 'sub', self::SUP => 'sup'];
                $open = [self::ITALIC => false, self::SUB => false, self::SUP => false];

                $count = array_count_values($arr);
                $inUrl = false;

                foreach ($arr as $index => $element) {
                    if ($element == 'www.' || preg_match('#^[\w]+?:#i', $element)) {
                        $inUrl = true;
                    } elseif ($element == ' ') {
                        $inUrl = false;
                    }

                    if (isset($replacement[$element])) {
                        if (!$open[$element] && $count[$element] > 1 && !$inUrl) {
                            if (isset($arr[$index + 1]) && $arr[$index + 1] != $element) {
                                $arr[$index] = $replacement[$element] !== '*' ? '<' . $replacement[$element] . '>' : '*';
                                $open[$element] = true;
                            }
                        } elseif ($open[$element]) {
                            $arr[$index] = $replacement[$element] !== '*' ? '</' . $replacement[$element] . '>' : '*';
                            $open[$element] = false;
                        }

                        $count[$element]--;
                    }
                }

                $line = implode('', $arr);
            }
        }

        return $this->joinLineBreaks($lines);
    }

    private function headline(string $text): string
    {
        $text = preg_replace_callback('#^(={1,6}) (.*?) \1(?=\s|$)#m', function ($matches) {
            $depth = mb_strlen($matches[1]);
            $title = trim($matches[2]);

            return str_repeat('#', $depth) . ' ' . $title;
        }, $text);

        $lines = $this->splitLineBreaks($text);

        foreach ($lines as $index => &$line) {
            if (preg_match('#^\~{1,}$#', $line, $matches)) {
                if (isset($lines[$index - 1])) {
                    $lines[$index - 1] = '### ' . $lines[$index - 1];
                    $line = '';
                }
            }
        }

        return $this->joinLineBreaks($lines);
    }

    private function quote(string $text): string
    {
        $text = str_replace("\r\n", "\n", $text);

        while (($start = $this->findNested($text, 'quote')) !== false) {
            $end = mb_strpos($text, '</quote>', $start);

            if ($end === false) {
                break;
            }

            $attr = null;

            $begin = mb_strpos($text, '>', $start) + 1;
            if (mb_substr($text, $start + 6, 1) === '=') {
                $attr = trim(mb_substr($text, $start + 7, $begin - $start - 8), '"');
            }

            $after = mb_substr($text, $end + mb_strlen('</quote>'));
            $before = mb_substr($text, 0, $start);
            $input = "\n> " . str_replace("\n", "\n> ", trim(mb_substr($text, $begin, $end - $begin))) . "\n";

            if ($attr) {
                if (is_numeric($attr)) {
                    $attr = "\n > ##### [" . $this->quote[$attr] . " napisał(a)](" . route('forum.share', $attr) . "):";
                } else {
                    $attr = "\n > ##### " . $attr . " napisał(a)";
                }
                $input = $attr . $input;
            }

            $text = $before . $input . $after;
        }

        return $text;
    }

    private function findNested($text, $tag)
    {
        $offset = mb_strpos($text, "<$tag");
        if ($offset === false) {
            return false;
        }

        $matches = $this->splitTags($text, $tag);

        foreach ($matches as $match) {
            $value = $match[0];
            $index = $match[1];

            if ($index > $offset) {
                if ($value === "</$tag>") {
                    break;
                } elseif ($value === "<$tag") {
                    $offset = $index;
                }
            }
        }

        return $offset;
    }

//    private function findClosingTag($text, $tag, $offset)
//    {
//        $matches = $this->splitTags($text, $tag);
//
//        $opening = 1;
//        $result = false;
//
//        foreach ($matches as $match) {
//            $value = $match[0];
//            $index = $match[1];
//
//            if ($index > $offset) {
//                if ($value === "</$tag>") {
//                    --$opening;
//                    $result = $index;
//
//                    if ($opening === 0) {
//                        break;
//                    }
//                } elseif ($value === "<$tag") {
//                    ++$opening;
//                }
//            }
//        }
//
//        return $result;
//    }

    private function splitTags($text, $tag)
    {
        $result = [];
        $offset = 0;

        while (($offset = mb_strpos($text, "<$tag", $offset)) !== false) {
            $result[] = [
                "<$tag",
                $offset
            ];

            $offset++;
        }

        $offset = 0;

        while (($offset = mb_strpos($text, "</$tag>", $offset)) !== false) {
            $result[] = [
                "</$tag>",
                $offset
            ];

            $offset++;
        }

        if (count($result) > 0) {
            foreach ($result as $key => $row) {
                $volume[$key]  = $row[1];
            }

            array_multisort($volume, SORT_ASC, $result);
        }

        return $result;
    }

    private function hashBacktick(string $text)
    {
        $searchFor = "`";

        $lines = $this->splitLineBreaks($text);

        foreach ($lines as &$line) {
            $start = 0;

            while (($start = mb_strpos($line, $searchFor, $start)) !== false) {
                $end = mb_strpos($line, $searchFor, $start + 1);

                if ($end === false) {
                    continue 2;
                }
                ++$end;

                $line = $this->hashPart($line, $start, $end);
            }
        }

        return $this->joinLineBreaks($lines);
    }

    private function removeCodeTag(string $text): string
    {
        $text = str_replace(['`<code><code></code>`', '`<code></code></code>`'], ['`<code>`', '`</code>`'], $text);
        $lines = $this->splitLineBreaks($text);

        foreach ($lines as &$line) {
            if ($line === '<code>' || $line === '</code>') {
                $line = '```';
                continue;
            }

            $offset = 0;
            // jezeli <code> i </code> znajduja sie w tej samej linii...
            while (($start = mb_strpos($line, '<code>', min($offset, mb_strlen($line)))) !== false && ($end = mb_strrpos($line, '</code>', $offset)) !== false) {
                // jezeli sa backticki na poczaktu lub na koncu - nie robimy nic
                $len = strlen('<code>');
                $offset = min(mb_strlen($line), $offset + $start + 1);

                if ($start > 0 && $line[$start - 1] === '`' && $line[$start + $len] === '`') {
                    continue;
                }

                // jezeli pomeidzy <code> a </code> znajduje sie jeszcze jeden zancznik <code ...
                // brzydki hack...
                if ((mb_strpos($line, '<code>', $start + 1) !== false) && ($firstOccur = mb_strpos($line, '</code>', $offset)) !== false) {
                    $end = min($end, $firstOccur);
                }

                $line = mb_substr_replace($line, '`', $start, $len);
                $line = mb_substr_replace($line, '`', $end - $len + 1, $len + 1);
            }

            if (($start = preg_match('|<code class="([a-z\+]+)">|i', $line, $match, PREG_OFFSET_CAPTURE)) === 1) {
                $start = $match[0][1];
                $found = $match[0][0];
                $lang  = $match[1][0];

                // tag <code... jest pierwszym w linii. czesto moze byc uzyty taki zapis `<code ... ktorego
                // nie chcemy parsowac z uwagi na backtick
                if ($start >= 1) {
                    continue;
                }

                $rest = mb_substr($line, mb_strlen($found));
                $line = '```' . $lang;

                if ('' !== $rest) {
                    $line .= "\n" . $rest;
                }
            }
        }

        return $this->joinLineBreaks($lines);
    }

    private function splitLineBreaks(string $text): array
    {
        return explode("\n", str_replace("\r\n", "\n", $text));
    }

    private function joinLineBreaks(array $lines): string
    {
        return implode("\n", $lines);
    }
}

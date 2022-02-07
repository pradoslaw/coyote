<?php

namespace Coyote\Services\Parser\Parsers;

use TRegx\CleanRegex\Pattern;

class Context extends Parser implements ParserInterface
{
    const HEADLINE_REGEXP = '<h([1-6])>(.*?)</h\1>';

    /**
     * @param string $text
     * @return string
     */
    public function parse(string $text): string
    {
        if (strpos($text, '{{CONTENT}}') === false) {
            return $text;
        }

        $text = $this->hashBlock($text, ['code', 'a']);
        $text = $this->hashInline($text, 'img');

        $pattern = Pattern::of(self::HEADLINE_REGEXP);
        $headlines = '';

        foreach ($pattern->match($text) as $match) {
            $origin = $match->text();
            $indent = $match->get(1);
            $label = $match->get(2);

            $numbering = $this->numbering($indent);

            $anchor = str_slug($label);
            $text = str_replace($origin, "<h$indent><a name=\"$anchor\"></a> $label</h$indent>", $text);

            $headlines .= str_repeat('&nbsp;', 5 * ($indent - 1)) . link_to('#' . $anchor, $numbering . ' ' . $label) . '<br>';
        }

        $text = str_replace('{{CONTENT}}', $headlines, $text);
        $text = $this->unhash($text);

        return $text;
    }

    /**
     * @param int $indent
     * @return string
     */
    private function numbering($indent)
    {
        $numbering = '';

        static $prev;
        static $sub = [];

        /* ponizsze instrukcje maja okreslic ile jest	naglowkow o	danym "zaglebieniu"	*/
        if ($indent) {
            $prev = $indent;
        }

        if ($prev && $indent > $prev) {
            $sub[$indent] = 0;
        } elseif ($indent < $prev) {
            $sub[$indent + 1] = 0;
        }

        if (isset($sub[$indent])) {
            $sub[$indent]++;
        } else {
            $sub[$indent] = 1;
        }

        $dot = false;

        /* ponizsza petla	ustala numerowanie - np. 1.1.2.2 */
        for ($j = 1; $j <= $indent; $j++) {
            if (!empty($sub[$j])) {
                if ($dot) {
                    $numbering .= '.';
                }
                $numbering .= $sub[$j];
                $dot = true;
            }
        }

        return $numbering;
    }
}

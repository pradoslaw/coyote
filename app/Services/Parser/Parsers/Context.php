<?php
namespace Coyote\Services\Parser\Parsers;

use TRegx\CleanRegex\Pattern;

class Context extends HashParser implements Parser
{
    public function parse(string $text): string
    {
        if (!\str_contains($text, '{{CONTENT}}')) {
            return $text;
        }
        return parent::parse($text);
    }

    protected function parseHashed(string $text): string
    {
        $pattern = Pattern::of('<h([1-6])>(.*?)</h\1>');
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

        return str_replace('{{CONTENT}}', $headlines, $text);
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
        } else {
            if ($indent < $prev) {
                $sub[$indent + 1] = 0;
            }
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

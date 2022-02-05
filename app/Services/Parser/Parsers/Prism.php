<?php

namespace Coyote\Services\Parser\Parsers;

class Prism implements ParserInterface
{
    const ALIAS = ['c#' => 'csharp', 'delphi' => 'pascal', 'asm' => 'asm6502', 'bf' => 'brainfuck', 'jquery' => 'javascript', 'vb' => 'visual-basic'];

    /**
     * @param string $text
     * @return string
     */
    public function parse(string $text): string
    {
        preg_match_all('|<pre><code class="([a-z\d#-]+)">(.+?)<\/code><\/pre>|s', $text, $matches);

        if (!$matches[1]) {
            return $text;
        }

        for ($i = 0, $count = count($matches[1]); $i < $count; $i++) {
            $class = $originalClass = $matches[1][$i];
            $code = &$matches[2][$i];

            // class may have prefix "language". omit it.
            if (substr($class, 0, 8) === 'language') {
                $class = substr($class, 9);
                $class = 'language-' . (self::ALIAS[$class] ?? $class);
            }

            if (count(explode("\n", $code)) >= 5) {
                $class .= ' line-numbers';
            }

            if ($class !== $originalClass) {
                $text = str_replace($this->tag($originalClass, $code), $this->tag($class, $code), $text);
            }
        }

        return $text;
    }

    /**
     * @param string $class
     * @param string $code
     * @return string
     */
    private function tag($class, $code)
    {
        return sprintf('<pre><code class="%s">%s</code></pre>', $class, $code);
    }
}

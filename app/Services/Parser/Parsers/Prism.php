<?php

namespace Coyote\Services\Parser\Parsers;

use TRegx\CleanRegex\Pattern;

class Prism implements ParserInterface
{
    const ALIAS = [
        '.net'   => 'dotnet',
        'asm'    => 'asm6502',
        'bf'     => 'brainfuck',
        'bat'    => 'batch',
        'clj'    => 'clojure',
        'c++'    => 'cpp',
        'c#'     => 'csharp',
        'f#'     => 'fsharp',
        'delphi' => 'objectpascal',
        'sh'     => 'shell',
        'ps'     => 'powershell',
        'rs'     => 'rust',
    ];

    public function parse(string $text): string
    {
        $pattern = Pattern::of('<pre><code class="([a-z\d#.+-]+)">(.+?)</code></pre>', 's');

        foreach ($pattern->match($text) as $match) {
            $class = $originalClass = $match->get(1);
            $code = $match->get(2);

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

    private function tag(string $class, string $code): string
    {
        return sprintf('<pre><code class="%s">%s</code></pre>', $class, $code);
    }
}

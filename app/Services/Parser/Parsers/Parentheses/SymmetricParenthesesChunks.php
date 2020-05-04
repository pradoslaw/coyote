<?php

namespace Coyote\Services\Parser\Parsers\Parentheses;

use Tests\Feature\Services\Parser\Parsers\Parentheses\SymmetricParenthesesChunksTest;

/**
 * If you read {@link SymmetricParenthesesChunksTest} you will see examples, of how this
 * class works.
 *
 * This class responsibility is to split an arbitrary string into chunks, by parentheses.
 * The resulting chunks will always have:
 *  - Symmetric parentheses (e.g. `()`, `(())`, `()()`, etc.)
 *  - Mismatched parentheses (e.g. `(()`, `())`, etc.)
 * But will never have asymmetric parentheses (e.g. `)(`, etc.)
 *
 * The resulting chunks are used in {@link ParenthesesParser} to "comb" them into actual
 * valid parenthesis expressions. This class can be thought of as an internal implementation.
 */
class SymmetricParenthesesChunks
{
    public function chunk(string $content): array
    {
        return $this->filterNonNull($this->makeChunks(pattern('([()])')->split($content)));
    }

    private function makeChunks(array $elements): array
    {
        $stack = [];
        $current = null;
        $nestLevel = 0;
        foreach ($elements as $element) {
            if ($element === '') {
                continue;
            }
            if ($element === '(') {
                $nestLevel++;
                $stack[] = $current;
                $current = $element;
                continue;
            }
            if ($element === ')') {
                $nestLevel--;
                if ($nestLevel > 0) {
                    $current .= $element;
                    continue;
                }
                if ($nestLevel === 0) {
                    $stack[] = $current . $element;
                    $current = null;
                    continue;
                }
                if ($nestLevel < 0) {
                    $stack[] = $current;
                    $stack[] = $element;
                    $current = null;
                    $nestLevel = 0;
                }
                continue;
            }
            $current .= $element;
        }
        $stack[] = $current;
        return $stack;
    }

    private function filterNonNull(array $elements): array
    {
        return array_values(array_filter($elements, function (?string $element) {
            return $element !== null;
        }));
    }
}

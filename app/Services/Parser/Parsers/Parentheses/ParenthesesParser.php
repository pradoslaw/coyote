<?php

namespace Coyote\Services\Parser\Parsers\Parentheses;

use Tests\Feature\Services\Parser\Parsers\Parentheses\ParenthesesParserTest;

/**
 * If you read {@link ParenthesesParserTest} you will see examples, of how this
 * class works.
 *
 * This class responsibility is to use symmetric or mismatched chunks returned
 * by {@link SymmetricParenthesesChunks} and "comb" them into valid/invalid
 * parentheses expressions. It is done in two steps:
 *  - Combing:
 *    - Iterates chunks first by pairs (if nestLevel is >= 0).
 *    - Then, iterates by triplets (if nestLevel is >= 1).
 *    - Then, iterates chunks by four of each (if nestLevel is >= 2), and so on
 *      Algorithm complexity is: o(n * c), where:
 *      - `n` - is the number of chunks, which is number of symmetric parentheses in input string divided by two.
 *      - `l` - nestLevel
 *      So for nestLevel=chunks (n=l), complexity is: o(n^2)
 *    After combing, `l` levels of parentheses should be joined into one. (So for example,
 *      for nestLevel=6, parentheses nested 7 times won't be parsed).
 *  - Squashing
 *    - Joins any two valid adjacent chunks into one, so they can be later for example
 *       rendered as a single link.
 */
class ParenthesesParser
{
    /** @var SymmetricParenthesesChunks */
    private $chunks;
    /** @var int */
    private $nestLevel;

    public function __construct(SymmetricParenthesesChunks $chunks, int $nestLevel)
    {
        $this->chunks = $chunks;
        $this->nestLevel = $nestLevel;
    }

    public function parse(string $content): array
    {
        $chunks = $this->chunks->chunk($content);
        $chunks = $this->comb($chunks, 1);
        $chunks = $this->flatMapMismatched($chunks);
        $chunks = $this->comb($chunks, $this->nestLevel - 1);
        $chunks = $this->squashChunks($chunks);
        return $chunks;
    }

    private function comb(array $elements, int $iterations): array
    {
        $result = $elements;
        for ($i = 0; $i < $iterations; $i++) {
            $result = $this->performCombIteration($result, $i + 1);
        }
        return $result;
    }

    private function performCombIteration(array $elements, int $nestLevel): array
    {
        // for $nestLevel < 0 this method doesn't make any sense
        // for $nestLevel = 0 this method doesn't have any effect
        if ($nestLevel < 0) {
            throw new \InvalidArgumentException();
        }
        $result = [];
        for ($i = 0; $i < count($elements); $i++) {
            $toComb = join(array_slice($elements, $i, $nestLevel + 1));
            if ($this->validExpression($toComb)) {
                $result[] = $toComb;
                $i += $nestLevel;
                continue;
            }
            $result[] = $elements[$i];
        }
        return $result;
    }

    private function flatMapMismatched(array $elements): array
    {
        $result = [];
        foreach ($elements as $element) {
            if ($this->validExpression($element)) {
                $result[] = $element;
            } else {
                $result = array_merge($result, pattern('([()])')->split($element));
            }
        }
        return array_values(array_filter($result, 'strlen'));
    }

    private function squashChunks(array $chunks): array
    {
        $result = [];
        $squashedChunks = null;
        foreach ($chunks as $chunk) {
            if ($this->validExpression($chunk)) {
                $squashedChunks .= $chunk;
            } else {
                if ($squashedChunks !== null) {
                    $result[] = $squashedChunks;
                    $squashedChunks = null;
                }
                $result[] = $chunk;
            }
        }
        if ($squashedChunks !== null) {
            $result[] = $squashedChunks;
        }
        return $result;
    }

    private function validExpression(string $expression): bool
    {
        return mb_substr_count($expression, '(') === mb_substr_count($expression, ')');
        // Technically, ")(" would also be considered a valid expression,
        // but `ParenthesesChunks` will not return such chunks.
    }
}

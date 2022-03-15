<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\WordRepositoryInterface as WordRepository;
use TRegx\SafeRegex\Exception\PregException;
use TRegx\SafeRegex\preg;

class Censore extends Parser implements ParserInterface
{
    public function __construct(private WordRepository $word)
    {
    }

    public function parse(string $text): string
    {
        static $result;

        $text = $this->hashBlock($text, ['code', 'a']);
        $text = $this->hashInline($text, 'img');

        if ($result === null) {
            $result = $this->word->allWords();
        }
        $words = [];

        foreach ($result as $row) {
            $word = '#(?<![\p{L}\p{N}_])' . str_replace('\*', '(\p{L}*?)', preg_quote($row->word)) . '(?![\p{L}\p{N}_])#iu';
            $words[$word] = $row->replacement;
        }

        try {
            $text = preg::replace(array_keys($words), array_values($words), $text);
        } catch (PregException $ignored) {
        }

        $text = $this->unhash($text);

        return $text;
    }
}

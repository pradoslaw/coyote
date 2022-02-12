<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\WordRepositoryInterface as WordRepository;

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

        $censoredText = preg_replace(array_keys($words), array_values($words), $text);

        // $censoredText can be null in case of error
        if (is_string($censoredText)) {
            $text = $censoredText;
        }

        $text = $this->unhash($text);

        return $text;
    }
}

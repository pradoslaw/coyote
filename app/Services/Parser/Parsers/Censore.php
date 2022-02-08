<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\WordRepositoryInterface as WordRepository;
use TRegx\CleanRegex\Pattern;

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

        $template = Pattern::template('(?<![\p{L}\p{N}_])@(?![\p{L}\p{N}_])', 'iu');

        foreach ($result as $row) {
            $pattern = $template->mask($row->word, ['*' => '(\p{L}*?)']);
            $word = $pattern->delimited();
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

<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\WordRepositoryInterface as WordRepository;

/**
 * Class Censore
 */
class Censore extends Parser implements ParserInterface
{
    /**
     * @var WordRepository
     */
    private $word;

    /**
     * @param WordRepository $word
     */
    public function __construct(WordRepository $word)
    {
        $this->word = $word;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
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

        $text = preg_replace(array_keys($words), array_values($words), $text);
        $text = $this->unhash($text);

        return $text;
    }
}

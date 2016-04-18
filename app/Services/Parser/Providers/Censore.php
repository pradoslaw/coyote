<?php

namespace Coyote\Services\Parser\Providers;

use Coyote\Repositories\Contracts\WordRepositoryInterface as Word;

/**
 * Class Censore
 */
class Censore implements ProviderInterface
{
    use Hash;

    /**
     * @var Word
     */
    private $word;

    /**
     * @param Word $word
     */
    public function __construct(Word $word)
    {
        $this->word = $word;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        $text = $this->hashBlock($text, ['code', 'a']);
        $text = $this->hashInline($text, 'img');

        $words = [];

        foreach ($this->word->all() as $row) {
            $word = '#(?<![\p{L}\p{N}_])' . str_replace('\*', '\p{L}*', preg_quote($row['word'], '#')) . '(?![\p{L}\p{N}_])#iu';
            $words[$word] = $row['replacement'];
        }

        $text = preg_replace(array_keys($words), array_values($words), $text);
        $text = $this->unhash($text);

        return $text;
    }
}

<?php

namespace Coyote\Parser\Providers;

use Coyote\Repositories\Contracts\WordRepositoryInterface as Word;

/**
 * Class Censore
 * @package Coyote\Parser\Providers
 */
class Censore implements ProviderInterface
{
    private $word;
    protected $hash = [];

    public function __construct(Word $word)
    {
        $this->word = $word;
    }

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

    /**
     * @param string $text
     * @param array|string $element
     * @param bool|false $inline
     * @return string
     */
    protected function hashElement($text, $element, $inline = false)
    {
        if (is_array($element)) {
            foreach ($element as $name) {
                $text = $this->hashElement($text, $name, $inline);
            }
        } else {
            while (($start = strpos($text, "<$element")) !== false) {
                // wyznaczamy pozycje zakonczenia znacznika. na pewno bedzie to znak > poniewaz inne
                // niedozwolone uzycie tego znaku zostanie zastapione przez &gt; przez purifier
                $end = strpos($text, '>', $start);

                if (!$inline) {
                    // w przypadku elementow blokowych wyznaczamy miejsce zakonczenia, tj. tag zamykajacy
                    if (($close = strpos($text, "</$element>")) !== false) {
                        $end = $close + strlen("</$element>");
                    }
                } else {
                    ++$end;
                }

                $text = $this->hashPart($text, $start, $end);
            }
        }

        return $text;
    }

    protected function hashBlock($text, $element)
    {
        return $this->hashElement($text, $element);
    }

    protected function hashInline($text, $element)
    {
        return $this->hashElement($text, $element, true);
    }

    /**
     * @param string $text
     * @param int $start
     * @param int $end
     * @return string
     */
    protected function hashPart($text, $start, $end)
    {
        $uniqId = uniqid('', true);
        $length = $end - $start;
        $match = substr($text, $start, $length);

        $text = substr_replace($text, $uniqId, $start, $length);
        $this->hash[$uniqId] = $match;

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function unhash($text)
    {
        if ($this->hash) {
            foreach ($this->hash as $uniqId => $data) {
                $text = str_replace($uniqId, $data, $text);
            }

            $this->hash = [];
        }

        return $text;
    }
}

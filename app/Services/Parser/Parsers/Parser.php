<?php

namespace Coyote\Services\Parser\Parsers;

abstract class Parser
{
    /**
     * @var array
     */
    protected $hash = [];

    /**
     * @param string $text
     * @param array|string $element
     * @return string
     */
    protected function hashBlock($text, $element)
    {
        return $this->hashElement($text, $element);
    }

    /**
     * @param string $text
     * @param array|string $element
     * @return string
     */
    protected function hashInline($text, $element)
    {
        return $this->hashElement($text, $element, true);
    }

    /**
     * Reverse hash (keep the right order).
     *
     * @param string $text
     * @return string
     */
    protected function unhash($text)
    {
        if (!empty($this->hash)) {
            while (count($this->hash) > 0) {
                end($this->hash); // set pointer to the last element

                $uniqId = key($this->hash); // key of the assoc array
                $text = str_replace($uniqId, array_pop($this->hash), $text);
            }
        }

        return $text;
    }

    /**
     * @param string $text
     * @param array|string $element
     * @param bool|false $inline
     * @return string
     */
    private function hashElement($text, $element, $inline = false)
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
}

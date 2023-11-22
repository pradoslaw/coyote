<?php
namespace Coyote\Services\Parser\Parsers;

abstract class HashParser
{
    protected array $hash = [];

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
                $text = $this->unhashLast($text);
            }
        }

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function unhashLast($text)
    {
        end($this->hash); // set pointer to the last element
        $uniqId = key($this->hash); // key of the assoc array

        $text = str_replace($uniqId, array_pop($this->hash), $text);

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
            $offset = 0;

            while (($start = mb_strpos($text, "<$element", $offset)) !== false) {
                $offset = $start + 1;
                // wyznaczamy pozycje zakonczenia znacznika. na pewno bedzie to znak > poniewaz inne
                // niedozwolone uzycie tego znaku zostanie zastapione przez &gt; przez purifier
                $end = mb_strpos($text, '>', $start);

                if ($end === false) {
                    continue;
                }

                if (!$inline) {
                    // w przypadku elementow blokowych wyznaczamy miejsce zakonczenia, tj. tag zamykajacy
                    if (($close = mb_strpos($text, "</$element>", $start)) !== false) {
                        $end = $close + mb_strlen("</$element>");
                    }
                } else {
                    ++$end;
                }

                $text = $this->hashPart($text, $start, $end);
            }
        }

        return $text;
    }

    protected function hashPart(string $text, int $start, int $end): string
    {
        $uniqId = uniqid('', true);
        $length = $end - $start;

        $match = mb_substr($text, $start, $length);

        $text = str_replace($match, $uniqId, $text);
        $this->hash[$uniqId] = $match;

        return $text;
    }
}

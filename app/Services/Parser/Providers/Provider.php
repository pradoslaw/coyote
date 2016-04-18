<?php

namespace Coyote\Services\Parser\Providers;

/**
 * Class Provider
 */
abstract class Provider
{
    private $hash = [];

    /**
     * Analizuje tekst, linia po linii i przekazuje zawartosc do $closure.
     * Istotne jest to, ze pomija ewentualne bloki kodu, tj. wszystko pomiedzy ` a ` oraz ```
     *
     * @param string $text
     * @param \Closure $closure
     * @return string
     */
    protected function processInline($text, \Closure $closure)
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = trim($text, "\n");
        $lines = explode("\n", $text);

        $isBlockCode = false;

        foreach ($lines as &$line) {
            if (empty($line)) {
                continue;
            }

            $firstChar = $line[0];
            if ($firstChar === '`' && strncmp($line, '```', 3) === 0 || $firstChar === '~'
                && strncmp($line, '~~~', 3) === 0) {
                // czy analizujemy wewnatrz bloku kodu czy nie?
                $isBlockCode = !$isBlockCode;
                continue;
            } elseif ($isBlockCode === true) {
                continue;
            } elseif (strncmp($line, '    ', 4) === 0 || $firstChar === "\t") {
                continue;
            } elseif (strpos($line, '`') !== false) {
                $raw = $this->hashInline($line);
                $line = $closure($raw);

                $line = $this->unhash($line);
            } else {
                $line = $closure($line);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param $line
     * @return string
     */
    protected function hashInline($line)
    {
        $raw = '';

        while ($haystack = strpbrk($line, '`')) {
            // pozycja znaku `
            $markerPosition = strpos($line, '`');

            // zwracamy hash
            $hash = $this->hashPart($haystack);
            if (!$hash) {
                $raw .= substr($line, 0, $markerPosition + 1);
                $line = substr($line, $markerPosition + 1);
            }

            // doklejamy do rezultatu tekst poprzedzajacy wystapienie znaku `
            $raw .= substr($line, 0, $markerPosition);
            // doklejamy hash zamiast oryginalnego tekstu (backtick)
            $raw .= $hash['hash'];

            // usuwamy z oryginalnej zmiennej porcje przetworzonego tekstu
            $line = substr($line, $markerPosition + $hash['extent']);
        }

        $raw .= $line;
        return $raw;
    }

    /**
     * @param $text
     * @return array|null
     */
    protected function hashPart($text)
    {
        if (preg_match('/^(`+)[ ]*(.+?)[ ]*(?<!`)\1(?!`)/s', $text, $matches)) {
            $uniqId = uniqid('', true);
            $this->hash[$uniqId] = $matches[0];

            return ['hash' => $uniqId, 'extent' => mb_strlen($matches[0])];
        } else {
            return null;
        }
    }

    /**
     * @param $text
     * @return mixed
     */
    protected function unhash($text)
    {
        if ($this->hash) {
            foreach ($this->hash as $uniqId => $data) {
                $text = str_replace($uniqId, $data, $text);
            }
        }

        return $text;
    }
}

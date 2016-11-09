<?php

namespace Coyote\Services\Parser\Parsers;

class Geshi implements ParserInterface
{
    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        preg_match_all('|<pre><code class="([a-z\d#-]+)">(.+?)<\/code><\/pre>|s', $text, $matches);

        if (!$matches[1]) {
            return $text;
        }

        $alias = ['html' => 'html5'];

        $geshi = new \Boduch\Geshi\Geshi();
        $geshi->line_ending = "\n";
        /* tekst bedzie zawarty w	znaczniku <pre>	*/
        $geshi->set_header_type(GESHI_HEADER_NONE);

        for ($i = 0, $count = count($matches[1]); $i < $count; $i++) {
            $language = $matches[1][$i];

            // class may have prefix "language". omit it.
            if (substr($language, 0, 8) === 'language') {
                $language = substr($language, 9);
            }

            if (isset($alias[$language])) {
                $language = $alias[$language];
            }

            if ($language) {
                /* nadaj jezyk kolorowania skladnii */
                $geshi->set_language($language, true);
            }

            $geshi->set_source(htmlspecialchars_decode($matches[2][$i]));
            $text = str_replace($matches[2][$i], $geshi->parse_code(), $text);
        }

        return $text;
    }
}

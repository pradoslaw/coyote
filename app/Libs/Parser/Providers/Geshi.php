<?php

namespace Coyote\Parser\Providers;

class Geshi implements ProviderInterface
{
    /**
     * @param string $text
     * @return mixed|string
     */
    public function parse($text)
    {
        preg_match_all('|<pre><code class="([a-z\d#-]+)">(.+?)<\/code><\/pre>|s', $text, $matches);

        if (!$matches[1]) {
            return $text;
        }

        $alias = ['html' => 'html5'];

        $geshi = new \Coyote\Parser\geshi('', '');
        $geshi->line_ending = "\n";
        /* tekst bedzie zawarty w	znaczniku <pre>	*/
        $geshi->set_header_type(GESHI_HEADER_NONE);

        for ($i = 0; $i < count($matches[1]); $i++) {
            $language = substr($matches[1][$i], 9);

            if (isset($alias[$language])) {
                $language = $alias[$language];
            }

            $geshi->set_source(htmlspecialchars_decode($matches[2][$i]));
            /* nadaj jezyk kolorowania skladnii */
            $geshi->set_language($language, true);

            $text = str_replace($matches[2][$i], $geshi->parse_code(), $text);
        }

        return $text;
    }
}

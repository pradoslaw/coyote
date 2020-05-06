<?php

namespace Tests\Feature\Helpers;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetKeywords()
    {
        // given
        $text = 'foo bar i18n bar foo i18n www.google.com ipsum i18n';

        // when
        $keywords = keywords($text, 4);

        // then
        $this->assertEquals(['i18n', 'foo', 'bar', 'wwwgooglecom'], $keywords);
    }

    /**
     * @test
     */
    public function shouldGetKeywords_forLimit0()
    {
        // given
        $text = 'foo bar i18n bar foo i18n www.google.com ipsum i18n';

        // when
        $keywords = keywords($text, 0);

        // then
        $this->assertEquals(['i18n', 'foo', 'bar', 'wwwgooglecom', 'ipsum'], $keywords);
    }

    /**
     * @test
     */
    public function shouldGetKeywords_forNegative()
    {
        // given
        $text = 'foo bar i18n bar foo i18n www.google.com ipsum i18n';

        // when
        $keywords = keywords($text, -4); // TODO InvalidArgumentException ?

        // then
        $this->assertEquals(['i18n', /*'foo', 'bar', 'wwwgooglecom', 'ipsum'*/], $keywords);
    }
}

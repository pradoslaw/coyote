<?php

namespace Tests\Feature\Services\Helpers;

use Carbon\Carbon;
use Coyote\Services\Helper\DateDifference;
use PHPUnit\Framework\TestCase;

class DateDifferenceTest extends TestCase
{
    /** @var string */
    private $locale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locale = Carbon::getLocale();
        Carbon::setLocale('pl');
    }

    protected function tearDown(): void
    {
        Carbon::setLocale($this->locale);

        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider dates
     */
    public function shouldGetDate_inHumanFormat(string $now, string $expected): void
    {
        // given
        Carbon::setTestNow($now);

        $difference = new DateDifference('%Y-%m-%d %H:%M', true);

        // when
        $text = $difference->format($this->date());

        // then
        $this->assertEquals($expected, $text);
    }

    public function dates(): array
    {
        return [
            '1 minute ago' => ['2017-1-1 12:35', '4 sekundy temu'],
            '1 hour ago'   => ['2017-1-1 13:26', '51 minut temu'],
            'today'        => ['2017-1-1 17:34', 'dziś, 12:34'],
            'yesterday'    => ['2017-1-2 04:34', 'wczoraj, 12:34'],
            'absolute'     => ['2017-11-15 12:35', '2017-01-01 12:34'],
        ];
    }

    /**
     * @test
     */
    public function shouldGetDate(): void
    {
        // given
        $difference = new DateDifference('%Y-%m-%d %H:%M', true);

        // when
        $text = $difference->format($this->date());

        // then
        $this->assertEquals('2017-01-01 12:34', $text);
    }

    private function date(): Carbon
    {
        return Carbon::parse('2017-01-01 12:34:56');
    }
}

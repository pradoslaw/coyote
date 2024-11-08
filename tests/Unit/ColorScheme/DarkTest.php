<?php
namespace Tests\Unit\ColorScheme;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\ColorScheme;

class DarkTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use ColorScheme\Fixture\ColorScheme;
    use ColorScheme\Fixture\Dark;

    /**
     * @test
     */
    public function default()
    {
        $this->assertFalse($this->isDark());
    }

    /**
     * @test
     */
    public function lastColorSchemeDark()
    {
        $this->setLastColorScheme('dark');
        $this->assertTrue($this->isDark());
    }

    /**
     * @test
     */
    public function lastColorSchemeLight()
    {
        $this->setLastColorScheme('light');
        $this->assertFalse($this->isDark());
    }
}

<?php
namespace Tests\Unit\ColorScheme;

use PHPUnit\Framework\TestCase;
use Tests\Unit\ColorScheme;
use Tests\Unit\BaseFixture;

class DarkTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use ColorScheme\Fixture\Dark;

    /**
     * @test
     */
    public function default()
    {
        $this->assertTrue($this->isDark());
    }

    /**
     * @test
     */
    public function dark()
    {
        $this->setDarkTheme(true);
        $this->assertTrue($this->isDark());
    }

    /**
     * @test
     */
    public function light()
    {
        $this->setDarkTheme(false);
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

    /**
     * @test
     */
    public function lastColorSchemeOverride()
    {
        $this->setLastColorScheme('light');
        $this->setDarkTheme(false);
        $this->assertFalse($this->isDark());
    }
}

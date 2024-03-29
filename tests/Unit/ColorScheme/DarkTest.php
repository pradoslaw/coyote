<?php
namespace ColorScheme;

use PHPUnit\Framework\TestCase;
use Tests\Unit\ColorScheme;

class DarkTest extends TestCase
{
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

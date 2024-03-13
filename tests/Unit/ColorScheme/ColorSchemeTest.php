<?php
namespace Tests\Unit\ColorScheme;

use PHPUnit\Framework\TestCase;
use Tests\Unit\ColorScheme;

class ColorSchemeTest extends TestCase
{
    use ColorScheme\Fixture\ColorScheme;

    /**
     * @test
     */
    public function defaultColorScheme()
    {
        $this->assertSame('system', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeLight()
    {
        $this->setColorScheme('light');
        $this->assertSame('light', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeDark()
    {
        $this->setColorScheme('dark');
        $this->assertSame('dark', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeSystem()
    {
        $this->setColorScheme('system');
        $this->assertSame('system', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeLegacyLight()
    {
        $this->setColorSchemeLegacy('light');
        $this->assertSame('light', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeLegacyDark()
    {
        $this->setColorSchemeLegacy('dark');
        $this->assertSame('dark', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeOverrideLegacy()
    {
        $this->setColorScheme('light');
        $this->setColorSchemeLegacy('dark');
        $this->assertSame('light', $this->colorScheme());
    }
}

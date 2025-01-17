<?php
namespace Tests\LookAndFeel\Theme;

class StyleGuide
{
    public function findNameByColor(string $rgbColor): string
    {
        $hex = $this->parseToHex($rgbColor);
        return $this->styleguideColors()[$hex] ?? $hex;
    }

    private function parseToHex(string $cssRgbPropertyValue): string
    {
        if (preg_match("/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?/", $cssRgbPropertyValue, $match)) {
            return \sPrintF('#%02x%02x%02x', $match[1], $match[2], $match[3]);
        }
        throw new \RuntimeException("Failed to parse rgb(): $cssRgbPropertyValue");
    }

    private function styleguideColors(): array
    {
        $content = $this->styleguideScss();
        $colorNamesByHex = ['#ffffff' => 'white'];
        $lines = \explode("\n", $content);
        foreach ($lines as $line) {
            if (empty(\trim($line))) {
                continue;
            }
            $withoutSemicolon = \rTrim($line, ';');
            $withoutDollar = \lTrim($withoutSemicolon, '$');
            [$colorName, $colorHex] = \explode(':', $withoutDollar);
            $colorNamesByHex[\trim(\strToLower(\trim($colorHex)))] = $colorName;
        }
        return $colorNamesByHex;
    }

    private static ?string $cachedScssContent = null;

    private function styleguideScss(): string
    {
        if (self::$cachedScssContent == null) {
            $content = file_get_contents(__DIR__ . '/../../../resources/feature/lookAndFeel/style-guide.scss');
            if ($content === false) {
                throw new \RuntimeException('Failed to parse ./style-guide.scss.');
            }
            self::$cachedScssContent = $content;
        }
        return self::$cachedScssContent;
    }
}

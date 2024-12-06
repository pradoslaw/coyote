<?php
namespace Coyote\Feature\LookAndFeel;

class StyleGuide
{
    public function getColors(): array
    {
        $content = file_get_contents('../resources/feature/lookAndFeel/style-guide.scss');
        $colors = [];
        $lines = \explode("\n", $content);
        foreach ($lines as $line) {
            if (empty(\trim($line))) {
                continue;
            }
            $withoutSemicolon = \rTrim($line, ';');
            $withoutDollar = \lTrim($withoutSemicolon, '$');
            [$key, $value] = \explode(':', $withoutDollar);
            $value = \trim($value);
            if ($value === 'white') {
                $value = '#ffffff';
            }
            $colors[$key] = \trim($value);
        }
        return $colors;
    }
}

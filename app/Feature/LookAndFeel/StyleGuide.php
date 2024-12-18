<?php
namespace Coyote\Feature\LookAndFeel;

class StyleGuide
{
    public function getPrimitiveColorGroups(): array
    {
        $content = file_get_contents('../resources/feature/lookAndFeel/style-guide.scss');
        $groups = [];
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
                continue;
            }
            $exploded = \explode('-', $key);
            if (count($exploded) === 1) {
                [$group, $groupValue] = [$exploded[0], $exploded[0]];
            } else {
                [$group, $groupValue] = $exploded;
            }
            $groups[$group]["$group-$groupValue"] = \trim(\strToUpper($value));
        }
        return $groups;
    }
}

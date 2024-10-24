<?php
namespace Coyote\Domain\Icon;

use Coyote\Domain\Html;
use Coyote\Domain\StringHtml;

readonly class Icons
{
    private FontAwesomePro $fa;

    public function __construct()
    {
        $this->fa = new FontAwesomePro();
    }

    public function icon(string $iconName): Html
    {
        return $this->iconWithClass($iconName, 'fa-fw');
    }

    public function iconSpin(string $iconName): Html
    {
        return $this->iconWithClass($iconName, 'fa-fw fa-spin');
    }

    private function iconWithClass(string $iconName, string $modifierClass): Html
    {
        $class = $this->iconClass($iconName);
        return $this->iconTag("$class $modifierClass", $iconName);
    }

    private function iconTag(string $class, string $iconName): Html
    {
        return new StringHtml(\sPrintF('<i class="%s" data-icon="%s"></i>', $class, $iconName));
    }

    private function iconClass(string $iconName): string
    {
        $icons = $this->fa->icons();
        if (\array_key_exists($iconName, $icons)) {
            return $icons[$iconName];
        }
        throw new \InvalidArgumentException("Failed to find icon: $iconName");
    }

    public function icons(): array
    {
        return $this->fa->icons();
    }
}

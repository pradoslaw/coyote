<?php
namespace Coyote\Domain\Icon;

use Coyote\Domain\Html;
use Coyote\Domain\StringHtml;

readonly class Icons
{
    private FontAwesomeFree $fa;

    public function __construct()
    {
        $this->fa = new FontAwesomeFree();
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
        return $this->iconTag("$class $modifierClass");
    }

    private function iconTag(string $class): Html
    {
        return new StringHtml(\sPrintF('<i class="%s"></i>', $class));
    }

    private function iconClass(string $iconName): string
    {
        $icons = $this->fa->icons();
        if (\array_key_exists($iconName, $icons)) {
            return $icons[$iconName];
        }
        throw new \InvalidArgumentException();
    }
}

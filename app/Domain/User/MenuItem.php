<?php
namespace Coyote\Domain\User;

use Coyote\Domain\Html;
use Coyote\Domain\Icon\Icons;

class MenuItem
{
    /** @var string[] */
    public array $route;
    public ?Html $icon = null;

    public function __construct(
        public string  $title,
        public string  $routeName,
        public ?string $subscript = null,
        public ?string $htmlId = null,
        public ?string $htmlClass = null,
        string         $icon = null,
    )
    {
        if ($icon) {
            $icons = new Icons();
            $this->icon = $icons->icon($icon);
        }
        $this->route = [$routeName];
    }
}

<?php
namespace Coyote\Domain\User;

class MenuItem
{
    /** @var string[] */
    public array $route;
    public ?string $htmlIcon = null;

    public function __construct(
        public string  $title,
        public string  $routeName,
        public ?string $subscript = null,
        public ?string $htmlId = null,
        public ?string $htmlClass = null,
        string         $htmlIcon = null,
    )
    {
        if ($htmlIcon) {
            $this->htmlIcon = "fa fa-fw $htmlIcon";
        }
        $this->route = [$routeName];
    }
}

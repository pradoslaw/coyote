<?php
namespace Coyote\Domain\User;

class MenuItem
{
    /** @var (string|int)[] */
    public array $route;
    public ?string $htmlIcon = null;

    public function __construct(
      public string  $title,
      public string  $routeName,
      array          $routeArguments = [],
      public ?string $htmlId = null,
      public ?string $htmlClass = null,
      string         $htmlIcon = null
    )
    {
        if ($htmlIcon) {
            $fixedWidth = 'fa-fw';
            $this->htmlIcon = "fa $fixedWidth $htmlIcon";
        }
        $this->route = [$routeName, ...$routeArguments];
    }
}

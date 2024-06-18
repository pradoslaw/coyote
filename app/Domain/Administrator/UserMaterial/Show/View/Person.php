<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Html;

abstract class Person
{
    public function __construct(
        public string  $name,
        public ?string $avatarUrl,
    )
    {
    }

    public abstract function mention(): Html;

    public function displayAvatar(): bool
    {
        return $this->avatarUrl !== null;
    }
}

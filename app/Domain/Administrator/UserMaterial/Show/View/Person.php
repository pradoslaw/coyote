<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Html;
use Coyote\Services\Media;

abstract class Person
{
    public function __construct(
        public string      $name,
        public ?Media\File $avatar,
    ) {}

    public abstract function mention(): Html;
}

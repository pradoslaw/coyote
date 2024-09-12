<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Services\Media;
use Coyote\Domain\Administrator\View\Mention;
use Coyote\Domain\Html;

class UserPerson extends Person
{
    public function __construct(
        private int $id,
        string      $name,
        ?Media\File  $avatar,
    )
    {
        parent::__construct($name, $avatar);
    }

    public function mention(): Html
    {
        return new Mention($this->id, $this->name);
    }
}

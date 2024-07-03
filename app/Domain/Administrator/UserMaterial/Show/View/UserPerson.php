<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Administrator\View\Mention;
use Coyote\Domain\Html;

class UserPerson extends Person
{
    public function __construct(
        private int $id,
        string      $name,
        string      $avatarUrl,
    )
    {
        parent::__construct($name, $avatarUrl);
    }

    public function mention(): Html
    {
        return new Mention($this->id, $this->name);
    }
}

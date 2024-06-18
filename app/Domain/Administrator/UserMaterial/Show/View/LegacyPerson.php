<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Html;
use Coyote\Domain\StringHtml;

class LegacyPerson extends Person
{
    public function __construct(string $name)
    {
        parent::__construct($name, null);
    }

    public function mention(): Html
    {
        return new StringHtml($this->name);
    }
}

<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Administrator\View\Mention;

class Person
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $avatarUrl,
    )
    {
    }

    public function mention(): Mention
    {
        return new Mention($this->id, $this->name);
    }
}

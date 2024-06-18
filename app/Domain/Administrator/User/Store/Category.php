<?php
namespace Coyote\Domain\Administrator\User\Store;

class Category
{
    public function __construct(
        public ?string $forumName,
        public int     $posts,
    )
    {
    }
}

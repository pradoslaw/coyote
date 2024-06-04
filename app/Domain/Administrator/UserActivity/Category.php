<?php
namespace Coyote\Domain\Administrator\UserActivity;

class Category
{
    public function __construct(
        public ?string $forumName,
        public int     $posts,
    )
    {
    }
}

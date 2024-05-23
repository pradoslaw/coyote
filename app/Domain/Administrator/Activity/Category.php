<?php
namespace Coyote\Domain\Administrator\Activity;

class Category
{
    public function __construct(
        public string $forumName,
        public int    $posts,
    )
    {
    }
}

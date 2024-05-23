<?php
namespace Coyote\Domain\Administrator\Activity;

class DeleteReason
{
    public function __construct(
        public ?string $reason,
        public int     $posts,
    )
    {
    }
}

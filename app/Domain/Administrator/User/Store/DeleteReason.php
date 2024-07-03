<?php
namespace Coyote\Domain\Administrator\User\Store;

class DeleteReason
{
    public function __construct(
        public ?string $reason,
        public int     $posts,
    )
    {
        if ($reason === '(użytkownik nie podał powodu)') {
            $this->reason = null;
        }
    }
}

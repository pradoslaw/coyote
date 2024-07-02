<?php
namespace Coyote\Domain\User;

class User
{
    public function __construct(
        public ?int $privateMessages,
        public ?int $privateMessagesUnread,
    )
    {
    }
}

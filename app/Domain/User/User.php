<?php
namespace Coyote\Domain\User;

class User
{
    public function __construct(
      public bool $loggedIn,
      public ?int $id,
      public ?int $privateMessages,
      public ?int $privateMessagesUnread,
    )
    {
    }
}

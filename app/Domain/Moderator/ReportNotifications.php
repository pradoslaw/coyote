<?php
namespace Coyote\Domain\Moderator;

use Coyote\Flag;
use Coyote\User;

class ReportNotifications
{
    public function __construct(private ?User $user)
    {
    }

    public function hasAccess(): bool
    {
        if ($this->user === null) {
            return false;
        }
        return $this->user->can('adm-access');
    }

    public function hasAny(): bool
    {
        return Flag::query()->exists();
    }

    public function count(): int
    {
        return Flag::query()->count();
    }
}

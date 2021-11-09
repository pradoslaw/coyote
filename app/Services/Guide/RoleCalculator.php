<?php

namespace Coyote\Services\Guide;

use Coyote\Guide;

class RoleCalculator
{
    public function __construct(private Guide $guide)
    {
    }

    public function setRole(int $userId, string $role): void
    {
        $currentRole = $this->guide->roles()->forUser($userId)->firstOrNew();

        $currentRole->role = $role;
        $currentRole->user_id = $userId;
        $currentRole->save();

        $this->guide->role = $this->calculateRole();
    }

    private function calculateRole(): string
    {
        $roles = array_count_values($this->guide->roles()->get(['role'])->pluck('role')->toArray());
        arsort($roles);

        return key($roles);
    }
}

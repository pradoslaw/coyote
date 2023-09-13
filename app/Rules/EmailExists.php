<?php

namespace Coyote\Rules;

use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Validation\Rule;

class EmailExists implements Rule
{
    public function __construct(private UserRepositoryInterface $user)
    {
    }

    public function passes($attribute, $value): bool
    {
        return $this->user->whereRaw("LOWER(email) = ?", [\mb_strToLower($value)])->exists();
    }

    public function message(): string
    {
        return \trans('validation.email_exists');
    }
}

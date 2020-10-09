<?php

namespace Coyote\Rules;

use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Validation\Rule;

class EmailExists implements Rule
{
    /**
     * @var UserRepositoryInterface
     */
    private $user;

    public function __construct(UserRepositoryInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $email): bool
    {
        return $this->user->whereRaw("LOWER(email) = ?", [mb_strtolower($email)])->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.email_exists');
    }
}

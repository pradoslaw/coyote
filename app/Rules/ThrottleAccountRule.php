<?php

namespace Coyote\Rules;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\PriorDate;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class ThrottleAccountRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(private UserRepository $repository, private Request $request)
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->repository->pushCriteria(new PriorDate(now()->subDay()));

        return $this->repository->findBy('ip', $this->request->ip()) === null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Uuup. Wygląda na to, że już się tutaj zarejestrowałeś.';
    }
}

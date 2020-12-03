<?php

namespace Coyote\Rules;

use Coyote\Reputation;
use Illuminate\Contracts\Validation\Rule;

class MinWords implements Rule
{
    private int $minWords;

    public function __construct(int $minWords = 3)
    {
        $this->minWords = $minWords;
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
        if (auth()->check() && auth()->user()->reputation >= Reputation::POSTING_SHORT_TITLE) {
            return true;
        }

        return count(array_filter(preg_split('/\s+/', $value), fn ($word) => strlen($word) > 1)) >= $this->minWords;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.min.words');
    }
}

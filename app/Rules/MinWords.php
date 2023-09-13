<?php

namespace Coyote\Rules;

use Coyote\Reputation;
use Illuminate\Contracts\Validation\Rule;

class MinWords implements Rule
{
    public function __construct(private int $minimumWords)
    {
    }

    public function passes($attribute, $value): bool
    {
        if (auth()->check() && auth()->user()->reputation >= Reputation::SHORT_TITLE) {
            return true;
        }
        return \count(\array_filter(\preg_split('/\s+/', $value), fn($word) => \strLen($word) > 1)) >= $this->minimumWords;
    }

    public function message(): string
    {
        return \trans('validation.min.words');
    }
}

<?php

namespace Coyote\Http\Requests\Forum;

use Illuminate\Contracts\Validation\Validator;

class SubjectRequest extends PostRequest
{
    public function rules()
    {
        return ['title' => $this->titleRule()];
    }

    public function withValidator(Validator $validator)
    {
        // overwrite parent method
    }
}

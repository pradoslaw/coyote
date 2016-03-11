<?php

namespace Coyote\Http\Requests;

class SubjectRequest extends PostRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ['subject' => self::RULE_SUBJECT];
    }
}
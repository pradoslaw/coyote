<?php

namespace Coyote\Http\Requests\Forum;

class SubjectRequest extends PostRequest
{
    public function rules()
    {
        return ['subject' => self::RULE_SUBJECT];
    }
}

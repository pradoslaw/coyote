<?php

namespace Coyote\Http\Forms\Forum;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;

class SubjectForm extends PostForm implements ValidatesWhenResolved
{
    public function buildForm()
    {
        $this->add('subject', 'text', [
            'rules' => self::RULE_SUBJECT
        ]);
    }
}

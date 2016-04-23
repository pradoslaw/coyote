<?php

namespace Coyote\Http\Forms\Forum;

use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class SubjectForm extends PostForm implements ValidatesWhenSubmitted
{
    public function buildForm()
    {
        $this->add('subject', 'text', [
            'rules' => self::RULE_SUBJECT
        ]);
    }
}

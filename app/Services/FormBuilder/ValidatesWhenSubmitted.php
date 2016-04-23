<?php

namespace Coyote\Services\FormBuilder;

interface ValidatesWhenSubmitted
{
    /**
     * Validate the given class instance.
     *
     * @return void
     */
    public function validate();
}

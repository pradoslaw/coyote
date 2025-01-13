<?php
namespace Tests\Legacy\IntegrationOld\Services\Form;

use Illuminate\Contracts\Validation\Validator;

class TestForm extends \Coyote\Services\FormBuilder\Form
{
    public function buildForm()
    {
    }

    protected function failedValidation(Validator $validator)
    {
        // don't throw validate exception
    }
}

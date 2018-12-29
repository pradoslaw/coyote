<?php

namespace Coyote\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                $this->existsRule(),
                'unique:users,email,NULL,id,is_confirm,1'
            ],
            'name'  => 'sometimes|username|exists:users'
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Ten adres e-mail jest już zweryfikowany.'
        ];
    }

    private function existsRule()
    {
        // validation rule only for guests
        if (!empty($this->user())) {
            return '';
        }

        return 'exists:users';
    }
}

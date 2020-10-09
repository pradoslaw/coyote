<?php

namespace Coyote\Http\Requests;

use Coyote\Rules\EmailExists;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                app(EmailExists::class),
                'email_confirmed'
            ]
        ];
    }
}

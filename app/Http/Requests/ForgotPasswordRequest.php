<?php

namespace Coyote\Http\Requests;

use Coyote\Repositories\Contracts\UserRepositoryInterface;
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
                new EmailExists(app(UserRepositoryInterface::class)),
                'email_confirmed'
            ]
        ];
    }
}

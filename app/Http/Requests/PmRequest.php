<?php

namespace Coyote\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use \Illuminate\Contracts\Validation\Validator;

class PmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'recipient'          => 'required|user_exist',
            'text'               => 'required'
        ];
    }

    /**
     * @param Validator $validator
     */
    protected function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            if (mb_strtolower($this->request->get('recipient')) === mb_strtolower(auth()->user()->name)) {
                $validator->errors()->add('recipient', trans('validation.custom.recipient.different'));
            }
        });
    }
}

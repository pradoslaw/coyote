<?php

namespace Coyote\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
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
            'email' => 'required|string|max:200|email',
            'name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:50',
            'github' => 'nullable|string|url',
            'text' => 'string|required|max:5000',
            'remember' => 'bool'
        ];
    }
}

<?php

namespace Coyote\Http\Requests;

use Coyote\Guide\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuideRequest extends FormRequest
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
            'title' => 'required|string|max:200',
            'excerpt' => 'nullable|string',
            'text' => 'required|string',
            'tags' => 'required|max:6',
            'role' => ['required', Rule::in([Role::JUNIOR, Role::MID, Role::SENIOR])],
            'tags.*.name'   => [
                'bail',
                'max:25',
                'tag',
                'tag_creation:300'
            ]
        ];
    }
}

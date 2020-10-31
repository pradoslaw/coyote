<?php

namespace Coyote\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MicroblogRequest extends FormRequest
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
        $microblog = $this->route('microblog');

        return [
            'parent_id'     => 'nullable|integer|exists:microblogs,id',
            'text'          => 'required|string|max:12000',
            'media.*.name'  => 'nullable|string'
        ];
    }
}

<?php

namespace Coyote\Http\Requests\Job;

use Illuminate\Foundation\Http\FormRequest;

class FirmRequest extends FormRequest
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
            'id' => 'nullable|integer',
            'is_private' => 'bool',
            'name' => 'required_if:is_private,0|max:60',
            'is_agency' => 'bool',
            'website' => 'nullable|url',
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'employees' => 'nullable|integer',
            'founded' => 'nullable|integer',
            'youtube_url' => 'nullable|string|max:255|url|host:youtube.com,youtu.be',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:50',
            'street_number' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return ['name.required_if' => 'Nazwa firmy jest wymagana.'];
    }
}

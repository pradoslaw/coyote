<?php

namespace Coyote\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkillsRequest extends FormRequest
{
    const RATE_LABELS = ['Słabo', 'Podstawy', 'Przeciętnie', 'Dobrze', 'Bardzo dobrze', 'Ekspert'];

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
            'name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if ($this->user()->skills()->where('tags.name', $value)->exists()) {
                        $fail('Taka umiejętność znajduje się już na Twojej liście.');
                    }
                },
            ],
            'priority' => 'required|integer|min:1|max:3'
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'     => 'Proszę wpisać nazwę umiejętności',
            'priority.min'      => 'Nie wprowadziłeś oceny swojej umiejętności.'
        ];
    }
}

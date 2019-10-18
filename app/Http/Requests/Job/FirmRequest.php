<?php

namespace Coyote\Http\Requests\Job;

use Illuminate\Foundation\Http\FormRequest;

class FirmRequest extends FormRequest
{
    const IS_AGENCY = 'bool';
    const WEBSITE = 'nullable|url';
    const DESCRIPTION = 'nullable|string';
    const YOUTUBE_URL = 'nullable|string|max:255|url|host:youtube.com,youtu.be';

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
            'is_agency' => self::IS_AGENCY,
            'website' => self::WEBSITE,
            'logo' => 'nullable|string',
            'description' => self::DESCRIPTION,
            'employees' => 'nullable|integer',
            'founded' => 'nullable|integer',
            'youtube_url' => self::YOUTUBE_URL,
            'latitude' => JobRequest::LOCATION_LATITUDE,
            'longitude' => JobRequest::LOCATION_LONGITUDE,
            'street' => JobRequest::LOCATION_STREET,
            'city' => JobRequest::LOCATION_CITY,
            'postcode' => 'nullable|string|max:50',
            'street_number' => JobRequest::LOCATION_STREET_NUMBER,
        ];
    }

    public function messages()
    {
        return ['name.required_if' => 'Nazwa firmy jest wymagana.'];
    }
}

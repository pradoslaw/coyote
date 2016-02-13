<?php

namespace Coyote\Http\Requests;

use Coyote\Http\Requests\Request;

/**
 * Walidacja ustawien uzytkownika
 *
 * Class UserSettingsRequest
 * @package Coyote\Http\Requests
 */
class UserSettingsRequest extends Request
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
            'email'                  => 'required|email|unique:users,email,' . auth()->user()->id,
            'website'                => 'url|reputation:50',
            'location'               => 'string|max:50',
            'birthyear'              => 'sometimes|integer|between:1950,2015',
            'about'                  => 'string|max:255',
            'sig'                    => 'string|max:255',
            'firm'                   => 'string|max:100',
            'position'               => 'string|max:100',
            'allow_count'            => 'boolean',
            'allow_smilies'          => 'boolean',
            'allow_subscribe'        => 'boolean',
            'allow_sig'              => 'boolean',
            'group_id'               => 'sometimes|integer|exists:group_users,group_id,user_id,' . auth()->user()->id
        ];
    }
}

<?php
namespace Coyote\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'asset' => 'required|mimes:' . config('filesystems.upload_mimes'),
        ];
    }

    public function messages(): array
    {
        return [
            'asset' => 'Załączony plik musi mieć format: :values.',
        ];
    }
}

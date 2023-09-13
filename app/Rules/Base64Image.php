<?php

namespace Coyote\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64Image implements Rule
{
    /** @var string[] */
    private array $validMimeTypes = ['image/png', 'image/jpeg'];
    private string $mimeType;

    public function passes($attribute, $value): bool
    {
        $mimeType = $this->imageMimeType($value);
        $this->mimeType = $mimeType;
        return in_array($mimeType, $this->validMimeTypes);
    }

    public function message(): string
    {
        $types = implode(', ', $this->validMimeTypes);
        return "The binary data must be a file of type: $types. '$this->mimeType' given.";
    }

    private function imageMimeType(string $base64Image): ?string
    {
        return \fInfo_buffer(\fInfo_open(), \base64_decode($base64Image), \FILEINFO_MIME_TYPE) ?? null;
    }
}

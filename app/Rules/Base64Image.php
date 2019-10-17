<?php

namespace Coyote\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64Image implements Rule
{
    private const VALID_MIME = ['image/png', 'image/jpeg'];

    /**
     * @var string
     */
    private $mime;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $image = base64_decode($value);
        $file = finfo_open();
        $result = finfo_buffer($file, $image, FILEINFO_MIME_TYPE);

        $this->mime = $result;

        return in_array($result, self::VALID_MIME);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return sprintf('The binary data must be a file of type: %s. %s given.', implode(', ', self::VALID_MIME), $this->mime);
    }
}

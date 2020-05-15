<?php

namespace Coyote\Services\Media;

use \Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Coyote\Services\Media\Factory as MediaFactory;

class Clipboard
{
    /**
     * @var ValidationFactory
     */
    private $validationFactory;

    /**
     * @var Factory
     */
    private $mediaFactory;

    public function __construct(ValidationFactory $validationFactory, MediaFactory $mediaFactory)
    {
        $this->validationFactory = $validationFactory;
        $this->mediaFactory = $mediaFactory;
    }

    public function paste(string $factory = 'screenshot'): MediaInterface
    {
        $input = file_get_contents("php://input");

        $validator = $this->validationFactory->make(
            ['length' => strlen($input)],
            ['length' => 'max:' . config('filesystems.upload_max_size') * 1024 * 1024]
        );

        $validator->validate();

        return $this->mediaFactory->make($factory)->put(file_get_contents('data://' . substr($input, 7)));
    }
}

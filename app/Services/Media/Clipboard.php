<?php

namespace Coyote\Services\Media;

use \Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Coyote\Services\Media\Factory as MediaFactory;

class Clipboard
{
    /**
     * @var Factory
     */
    private Factory $mediaFactory;

    public function __construct(MediaFactory $mediaFactory)
    {
        $this->mediaFactory = $mediaFactory;
    }

    public function paste(string $factory = 'screenshot'): MediaInterface
    {
        $input = file_get_contents("php://input");

        return $this->mediaFactory->make($factory)->put(file_get_contents('data://' . substr($input, 7)));
    }
}

<?php

namespace Coyote\Services\Thumbnail;

use Coyote\Services\Thumbnail\Objects\ObjectInterface;

class Url extends Proxy
{
    /**
     * @param string $path
     * @return string
     * @throws \Exception
     */
    public function make($path)
    {
        if (!$this->object instanceof ObjectInterface) {
            throw new \Exception('$object must implement ObjectInterface');
        }

        return $this->imageManager->url($path, $this->object->getWidth(), $this->object->getHeight());
    }
}

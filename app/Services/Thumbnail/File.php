<?php

namespace Coyote\Services\Thumbnail;

use Coyote\Services\Thumbnail\Objects\ObjectInterface;

class File extends Proxy
{
    /**
     * @param string $path
     * @return mixed
     * @throws \Exception
     */
    public function make($path)
    {
        if (!$this->object instanceof ObjectInterface) {
            throw new \Exception('$object must implement ObjectInterface');
        }
        
        $thumbnail = $this->imageManager->open($path)->thumbnail(
            $this->object->getBox(),
            $this->object->getInterface()
        );

        return $thumbnail->save($path);
    }
}

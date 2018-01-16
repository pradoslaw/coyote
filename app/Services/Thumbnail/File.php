<?php

namespace Coyote\Services\Thumbnail;

use Coyote\Services\Thumbnail\Objects\ObjectInterface;
use Intervention\Image\Constraint;

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

        $image = $this->imageManager->make($path);

        $image
            ->resize($this->object->getWidth(), $this->object->getHeight(), function (Constraint $constraint) {
                $constraint->aspectRatio();
            })
            ->resizeCanvas($this->object->getWidth(), $this->object->getHeight(), 'center', false);

        return $image->save($path);
    }
}

<?php

namespace Coyote\Services\Thumbnail;

use Coyote\Services\Thumbnail\Objects\ObjectInterface;
use Folklore\Image\ImageManager;

/**
 * Class Thumbnail
 * @package Coyote\Services\Thumbnail
 */
class Thumbnail
{
    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var ObjectInterface
     */
    protected $object;

    /**
     * @param ImageManager $imageManager
     */
    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * @param ObjectInterface $object
     * @return $this
     */
    public function setObject(ObjectInterface $object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @param $path
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

        $thumbnail->save($path);
    }
}

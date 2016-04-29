<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Http\Factories\ThumbnailFactory;
use Coyote\Services\Media\MediaInterface;
use Coyote\Services\Media\UserPhoto;
use Coyote\Services\Thumbnail\Objects\Photo;
// don't remove below line
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserPhotoFactory extends AbstractFactory
{
    use ThumbnailFactory;
    
    /**
     * @return MediaInterface
     */
    public function getMedia() : MediaInterface
    {
        return new UserPhoto($this->filesystem);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return MediaInterface
     */
    public function upload(UploadedFile $uploadedFile)
    {
        $media = parent::upload($uploadedFile);

        $this->getThumbnailFactory()->setObject(new Photo())->make($media->path());
        return $media;
    }
}

<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Http\Factories\ThumbnailFactory;
use Coyote\Services\Media\Logo;
use Coyote\Services\Media\MediaInterface;
// don't remove below line
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LogoFactory extends AbstractFactory
{
    use ThumbnailFactory;
    
    /**
     * @return MediaInterface
     */
    public function getMedia() : MediaInterface
    {
        return new Logo($this->filesystem);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return MediaInterface
     * @throws \Exception
     */
    public function upload(UploadedFile $uploadedFile)
    {
        $media = parent::upload($uploadedFile);

        $this->getThumbnailFactory()->setObject(new \Coyote\Services\Thumbnail\Objects\Logo())->make($media->path());
        return $media;
    }
}

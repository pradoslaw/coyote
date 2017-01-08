<?php

namespace Coyote\Services\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Logo extends File
{
    /**
     * @param UploadedFile $uploadedFile
     * @return MediaInterface
     */
    public function upload(UploadedFile $uploadedFile)
    {
        parent::upload($uploadedFile);

        $this->thumbnail->file(new \Coyote\Services\Thumbnail\Objects\Logo())->make($this->path());

        return $this;
    }
}

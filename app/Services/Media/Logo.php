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

        $this->applyFilter(new \Coyote\Services\Media\Filters\Logo());

        return $this;
    }
}

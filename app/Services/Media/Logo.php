<?php

namespace Coyote\Services\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Coyote\Services\Media\Filters\Logo as Filter;

class Logo extends File
{
    /**
     * @param UploadedFile $uploadedFile
     * @return MediaInterface
     */
    public function upload(UploadedFile $uploadedFile)
    {
        parent::upload($uploadedFile);

        $this->applyFilter(new Filter());

        return $this;
    }
}

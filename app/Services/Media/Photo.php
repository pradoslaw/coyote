<?php

namespace Coyote\Services\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Coyote\Services\Media\Filters\Logo as Filter;

class Photo extends File
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

    /**
     * @param mixed $content
     * @return MediaInterface
     */
    public function put($content)
    {
        parent::put($content);

        $this->applyFilter(new Filter());

        return $this;
    }
}

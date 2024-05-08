<?php

namespace Coyote\Services\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Photo extends File
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

    /**
     * @param mixed $content
     * @return MediaInterface
     */
    public function put($content)
    {
        parent::put($content);

        $this->applyFilter(new \Coyote\Services\Media\Filters\Logo());

        return $this;
    }
}

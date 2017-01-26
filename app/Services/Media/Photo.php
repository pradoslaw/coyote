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
        $this->resize();

        return $this;
    }

    /**
     * @param mixed $content
     * @return MediaInterface
     */
    public function put($content)
    {
        parent::put($content);
        $this->resize();

        return $this;
    }

    private function resize()
    {
        $this->thumbnail->file(new \Coyote\Services\Thumbnail\Objects\Photo())->make($this->path());
    }
}

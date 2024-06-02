<?php
namespace Coyote\Services\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Logo extends File
{
    public function upload(UploadedFile $uploadedFile)
    {
        parent::upload($uploadedFile);
        $this->applyFilter(new Filters\Logo());
        return $this;
    }
}

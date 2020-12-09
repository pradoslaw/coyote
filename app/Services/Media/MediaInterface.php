<?php

namespace Coyote\Services\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface MediaInterface
{
    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $filename
     */
    public function setFilename($filename);

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param bool|null $secure
     * @return Url
     */
    public function url($secure = null);

    /**
     * Return full path (example: /var/www/makana.pl/storage/uploads/maps/12345.jpg)
     *
     * @param string|null $filename
     * @return string
     */
    public function path($filename = null);

    /**
     * @return mixed
     */
    public function get();

    /**
     * @return int
     */
    public function size();

    /**
     * @param UploadedFile $uploadedFile
     * @return MediaInterface
     */
    public function upload(UploadedFile $uploadedFile);

    /**
     * @return bool
     */
    public function delete();

    /**
     * @return bool
     */
    public function isImage();
}

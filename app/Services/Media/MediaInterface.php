<?php

namespace Coyote\Services\Media;

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
     * @return string
     */
    public function path();

    /**
     * @return string
     */
    public function url();

    /**
     * @param mixed $content
     */
    public function put($content);

    /**
     * @return mixed
     */
    public function get();

    /**
     * @return int
     */
    public function size();

    /**
     * @return bool
     */
    public function isImage();

    /**
     * @return bool
     */
    public function delete();
}

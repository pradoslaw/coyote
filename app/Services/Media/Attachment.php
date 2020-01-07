<?php

namespace Coyote\Services\Media;

class Attachment extends File
{
    /**
     * @var string
     */
    protected $downloadUrl;

    /**
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    /**
     * @param string $downloadUrl
     * @return $this
     */
    public function setDownloadUrl($downloadUrl)
    {
        $this->downloadUrl = $downloadUrl;

        return $this;
    }

    /**
     * @param null|bool $secure
     * @return Url
     */
    public function url($secure = null)
    {
        // legacy code. it is possible to set alternative url in wiki module. consider removing it
        if ($this->downloadUrl && !$this->isImage()) {
            return $this->getDownloadUrl();
        }

        return (new Url($this->imageManager, $this))->secure($secure);
    }
}

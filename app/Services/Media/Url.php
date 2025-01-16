<?php
namespace Coyote\Services\Media;

class Url
{
    /**
     * @var null|bool
     */
    protected $secure;

    public function __construct(private File $file)
    {
    }

    /**
     * @param bool|null $flag
     * @return $this
     */
    public function secure($flag)
    {
        $this->secure = $flag;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->makeUrl();
    }

    /**
     * @return string
     */
    private function makeUrl()
    {
        if (!$this->file->getFilename()) {
            return ''; // because __toString() requires string value
        }
        return $this->withoutHostname($this->file->getFilesystem()->url($this->file->path()));
    }

    private function withoutHostname($url): string
    {
        return \parse_url($url)['path'];
    }
}

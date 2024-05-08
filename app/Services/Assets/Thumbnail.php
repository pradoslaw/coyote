<?php

namespace Coyote\Services\Assets;

use Coyote\Services\Media\ImageWizard;
use Illuminate\Contracts\Filesystem\Filesystem;

class Thumbnail
{
    private \Coyote\Services\Media\Filters\Thumbnail $filter;
    private string $path;

    public function __construct(
        private ImageWizard $wizard,
        private Filesystem  $filesystem,
    )
    {
    }

    public function setFilter(\Coyote\Services\Media\Filters\Thumbnail $filter): Thumbnail
    {
        $this->filter = $filter;
        return $this;
    }

    public function open(string $path): Thumbnail
    {
        $this->path = $path;
        return $this;
    }

    public function store(string $path): void
    {
        $data = $this->filesystem->get($this->path);
        $image = $this->wizard->resizedImage($this->filter, $data);
        $this->filesystem->put($path, $image);
    }
}

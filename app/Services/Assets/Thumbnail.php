<?php

namespace Coyote\Services\Assets;

use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\ImageManager;

class Thumbnail
{
    private FilterInterface $filter;
    private string $path;

    public function __construct(
        private ImageManager $imageManager,
        private Filesystem   $filesystem,
    )
    {
    }

    public function setFilter(FilterInterface $filter): Thumbnail
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
        $image = $this->imageManager->make($this->filesystem->get($this->path));
        $this->filesystem->put($path, $image->filter($this->filter)->encode());
    }
}

<?php

namespace Coyote\Services\Assets;

use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\ImageManager;

class Thumbnail
{
    private Filesystem $filesystem;
    private FilterInterface $filter;
    private ImageManager $imageManager;
    private string $path;

    public function __construct(ImageManager $imageManager, Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->imageManager = $imageManager;
    }

    public function setFilter(FilterInterface $filter): Thumbnail
    {
        $this->filter = $filter;

        return $this;
    }

    public function open(string $path)
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

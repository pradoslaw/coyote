<?php
namespace Coyote\Services\Media;

use Coyote\Services\Media\Filters\Thumbnail;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class ImageWizard
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function resizedImage(Thumbnail $thumbnail, ?string $data): string
    {
        return $this->resized($this->manager->read($data), $thumbnail)->encode();
    }

    private function resized(ImageInterface $image, Thumbnail $thumbnail): ImageInterface
    {
        if ($thumbnail->width === null) {
            return $image->scaleDown(height:$thumbnail->height);
        }
        return $image->pad($thumbnail->width, $thumbnail->height);
    }
}

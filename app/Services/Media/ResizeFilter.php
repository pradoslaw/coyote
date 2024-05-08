<?php
namespace Coyote\Services\Media;

use Intervention\Image\Constraint;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

readonly class ResizeFilter implements FilterInterface
{
    public function __construct(private ?int $width, private int $height)
    {
    }

    public function applyFilter(Image $image): Image
    {
        return $image
            ->resize($this->width, $this->height, fn(Constraint $constraint) => $constraint->aspectRatio())
            ->resizeCanvas($this->width, $this->height);
    }
}

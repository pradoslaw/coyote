<?php

namespace Coyote\Services\Media\Filters;

use Intervention\Image\Constraint;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

abstract class Thumbnail implements FilterInterface
{
    /**
     * @var int
     */
    protected $width;

    /**
     * @var
     */
    protected $height;

    /**
     * @param Image $image
     * @return Image
     */
    public function applyFilter(Image $image)
    {
        return $image
            ->resize($this->width, $this->height, function (Constraint $constraint) {
                $constraint->aspectRatio();
            })
            ->resizeCanvas($this->width, $this->height, 'center', false);
    }
}

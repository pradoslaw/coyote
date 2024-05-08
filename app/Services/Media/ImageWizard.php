<?php
namespace Coyote\Services\Media;

use Coyote\Services\Media\Filters\Thumbnail;
use Intervention\Image\ImageManager;

class ImageWizard
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager([
            'driver'                    => 'gd',
            'host'                      => env('CDN') ? '//' . env('CDN') : '',
            'src_dirs'                  => [],
            'url_parameter'             => '-image({options})',
            'url_parameter_separator'   => '-',
            'serve_image'               => true,
            'serve_custom_filters_only' => false,
            'write_image'               => true,
            'memory_limit'              => '128M',
        ]);
    }

    public function resizedImage(Thumbnail $thumbnail, ?string $data): string
    {
        return $this->manager
            ->make($data)
            ->filter(new ResizeFilter($thumbnail->width, $thumbnail->height))
            ->encode();
    }
}

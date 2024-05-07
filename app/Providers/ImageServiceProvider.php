<?php
namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

class ImageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('image', $this->imageManager(...));
        $this->app->alias('image', ImageManager::class);
    }

    private function imageManager(): ImageManager
    {
        return new ImageManager([
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
}

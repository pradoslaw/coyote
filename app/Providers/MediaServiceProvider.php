<?php

namespace Coyote\Providers;

use Coyote\Http\Factories\FilesystemFactory;
use Coyote\Services\Media\Factories\AttachmentFactory;
use Coyote\Services\Media\Factories\LogoFactory;
use Coyote\Services\Media\Factories\ScreenshotFactory;
use Coyote\Services\Media\Factories\UserPhotoFactory;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    use FilesystemFactory;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('media.attachment', function ($app) {
            return new AttachmentFactory($this->getFilesystemFactory(), $app['thumbnail']);
        });

        $this->app->singleton('media.logo', function ($app) {
            return new LogoFactory($this->getFilesystemFactory(), $app['thumbnail']);
        });

        $this->app->singleton('media.screenshot', function ($app) {
            return new ScreenshotFactory($this->getFilesystemFactory(), $app['thumbnail']);
        });

        $this->app->singleton('media.user_photo', function ($app) {
            return new UserPhotoFactory($this->getFilesystemFactory(), $app['thumbnail']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['media.attachment', 'media.logo', 'media.screenshot', 'media.user_photo'];
    }
}

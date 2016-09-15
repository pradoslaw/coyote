<?php

namespace Coyote\Providers;

use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * @var array
     */
    private $provides = [];

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $files = (new Filesystem())->allFiles(app_path('Repositories/Contracts'));

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $path = str_replace(app_path() . '/', '', $file->getPathname());
            $this->provides[] = 'Coyote\\' . str_replace('/', '\\', substr($path, 0, -4));
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            array_pull($this->provides, array_search(StreamRepositoryInterface::class, $this->provides)),
            'Coyote\\Repositories\\Mongodb\\StreamRepository'
        );

        foreach ($this->provides as $interface) {
            $segments = explode('\\', $interface);
            $repository = substr((string) array_pop($segments), 0, -9);

            $this->app->bind(
                $interface,
                implode('\\', array_merge(array_set($segments, 2, 'Eloquent'), [$repository]))
            );
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return $this->provides;
    }
}

<?php
namespace Coyote\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

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

        foreach ($files as $file) {
            $path = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $this->provides[] = 'Coyote\\' . str_replace('/', '\\', substr($path, 0, -4));
        }
    }

    public function register(): void
    {
        foreach ($this->provides as $interface) {
            $segments = explode('\\', $interface);
            $repository = substr((string)array_pop($segments), 0, -9);

            $this->app->singleton(
                $interface,
                implode('\\', array_merge(array_set($segments, 2, 'Eloquent'), [$repository])),
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

<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
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
        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\StreamRepositoryInterface',
            'Coyote\\Repositories\\Mongodb\\StreamRepository'
        );

        $this->bind('', $this->eloquent());
    }

    /**
     * @param $folder
     * @param $repositories
     */
    private function bind($folder, $repositories)
    {
        if ($folder) {
            $folder = '\\' . $folder;
        }

        foreach ($repositories as $key => $name) {
            if (is_array($name)) {
                $this->bind($key, $name);
            } else {
                $repository = "${name}Repository";

                $this->app->bind(
                    "Coyote\\Repositories\\Contracts$folder\\${repository}Interface",
                    "Coyote\\Repositories\\Eloquent$folder\\$repository"
                );

                $this->app->bind(
                    $repository,
                    "Coyote\\Repositories\\Eloquent$folder\\$repository"
                );
            }
        }
    }

    /**
     * Repositories to bind
     *
     * @return array
     */
    private function eloquent()
    {
        return [
            'Flag',
            'Word',
            'Tag',
            'Topic',
            'Forum',
            'Pm',
            'Session',
            'Setting',
            'Alert',
            'Reputation',
            'Microblog',
            'User',
            'Post',
            'Page',
            'Firewall',
            'Job',
            'Firm',

            'Post' => [
                'Attachment',
                'Log',
                'Accept',
                'Vote',
                'Comment'
            ],

            'Forum' => [
                'Order'
            ]
        ];
    }
}

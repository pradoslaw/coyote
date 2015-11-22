<?php namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('username', 'Coyote\UsernameValidator@validateUsername');
        Validator::extend('password', 'Coyote\PasswordValidator@validatePassword');
        Validator::extend('reputation', 'Coyote\ReputationValidator@validateReputation');
        Validator::replacer('reputation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\UserRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\UserRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\MicroblogRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\MicroblogRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\ReputationRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\ReputationRepository'
        );
    }
}

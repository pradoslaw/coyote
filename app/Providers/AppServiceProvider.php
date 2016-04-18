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
        Validator::extend('username', 'Coyote\UserValidator@validateName');
        Validator::extend('user_unique', 'Coyote\UserValidator@validateUnique');
        Validator::extend('user_exist', 'Coyote\UserValidator@validateExist');
        Validator::extend('password', 'Coyote\PasswordValidator@validatePassword');
        Validator::extend('reputation', 'Coyote\ReputationValidator@validateReputation');
        Validator::extend('tag', 'Coyote\TagValidator@validateTag');
        Validator::extend('tag_creation', 'Coyote\TagCreationValidator@validateTag');
        Validator::extend('throttle', 'Coyote\ThrottleValidator@validateThrottle');
        Validator::extend('city', 'Coyote\CityValidator@validateCity');

        Validator::replacer('reputation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        Validator::replacer('tag_creation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        if (strpos(php_sapi_name(), 'cli') === false) {
            // show mongodb queries in laravel debugbar
            \DB::connection('mongodb')->enableQueryLog();
        }
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
        $this->app->singleton('Setting', function ($app) {
            return new $app['Coyote\\Repositories\\Contracts\\SettingRepositoryInterface']($app);
        });

        $this->app->bind('Stream', function ($app) {
            return new $app['Coyote\\Services\\Stream\\Stream']($app['Coyote\\Repositories\\Contracts\\StreamRepositoryInterface']);
        });
    }
}

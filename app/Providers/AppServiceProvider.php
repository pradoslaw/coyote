<?php namespace Coyote\Providers;

use Coyote\Services\FormBuilder\FormBuilder;
use Coyote\Services\FormBuilder\FormInterface;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Redirector;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['validator']->extend('username', 'Coyote\UserValidator@validateName');
        $this->app['validator']->extend('user_unique', 'Coyote\UserValidator@validateUnique');
        $this->app['validator']->extend('user_exist', 'Coyote\UserValidator@validateExist');
        $this->app['validator']->extend('password', 'Coyote\PasswordValidator@validatePassword');
        $this->app['validator']->extend('reputation', 'Coyote\ReputationValidator@validateReputation');
        $this->app['validator']->extend('tag', 'Coyote\TagValidator@validateTag');
        $this->app['validator']->extend('tag_creation', 'Coyote\TagCreationValidator@validateTag');
        $this->app['validator']->extend('throttle', 'Coyote\ThrottleValidator@validateThrottle');
        $this->app['validator']->extend('city', 'Coyote\CityValidator@validateCity');

        $this->app['validator']->replacer('reputation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        $this->app['validator']->replacer('tag_creation', function ($message, $attribute, $rule, $parameters) {
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

        $this->app->singleton('form.builder', function ($app) {
            return new FormBuilder($app);
        });

        $this->app['events']->listen(RouteMatched::class, function () {
            $this->app->resolving(function (FormInterface $form, $app) {
                $form->setContainer($app)
                    ->setRedirector($app->make(Redirector::class))
                    ->setRequest($app->make('request'));

                if ($form instanceof ValidatesWhenResolved && $form->isSubmitted()) {
                    $form->buildForm();
                }
            });
        });
    }
}

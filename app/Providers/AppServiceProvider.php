<?php namespace Coyote\Providers;

use Coyote\Repositories\Contracts\SettingRepositoryInterface;
use Coyote\Services\FormBuilder\FormBuilder;
use Coyote\Services\FormBuilder\FormInterface;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
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
        // set cloud flare as trusted proxy
        $this->app['request']->setTrustedProxies($this->app['config']->get('cloudflare.ip'));
        // force HTTPS according to cloudflare HTTP_X_FORWARDED_PROTO header
        $this->app['request']->server->set(
            'HTTPS',
            $this->app['request']->server('HTTP_X_FORWARDED_PROTO') === 'https'
        );

        $this->app['validator']->extend('username', 'Coyote\Http\Validators\UserValidator@validateName');
        $this->app['validator']->extend('user_unique', 'Coyote\Http\Validators\UserValidator@validateUnique');
        $this->app['validator']->extend('user_exist', 'Coyote\Http\Validators\UserValidator@validateExist');
        $this->app['validator']->extend('password', 'Coyote\Http\Validators\PasswordValidator@validatePassword');
        $this->app['validator']->extend('reputation', 'Coyote\Http\Validators\ReputationValidator@validateReputation');
        $this->app['validator']->extend('tag', 'Coyote\Http\Validators\TagValidator@validateTag');
        $this->app['validator']->extend('tag_creation', 'Coyote\Http\Validators\TagValidator@validateTagCreation');
        $this->app['validator']->extend('throttle', 'Coyote\Http\Validators\ThrottleValidator@validateThrottle');
        $this->app['validator']->extend('city', 'Coyote\Http\Validators\CityValidator@validateCity');
        $this->app['validator']->extend('wiki_unique', 'Coyote\Http\Validators\WikiValidator@validateUnique');
        $this->app['validator']->extend('wiki_route', 'Coyote\Http\Validators\WikiValidator@validateRoute');
        $this->app['validator']->extend('email_unique', 'Coyote\Http\Validators\EmailValidator@validateUnique');
        $this->app['validator']->extend('email_confirmed', 'Coyote\Http\Validators\EmailValidator@validateConfirmed');

        $this->app['validator']->replacer('reputation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        $this->app['validator']->replacer('tag_creation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        if (strpos(php_sapi_name(), 'cli') === false) {
            // show mongodb queries in laravel debugbar
            $this->app['db']->connection('mongodb')->enableQueryLog();
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
        $this->app->singleton('setting', function ($app) {
            return new $app[SettingRepositoryInterface::class]($app);
        });

        $this->app->singleton('form.builder', function ($app) {
            return new FormBuilder($app);
        });

        $this->app['events']->listen(RouteMatched::class, function () {
            $this->app->resolving(function (FormInterface $form, $app) {
                $form->setContainer($app)
                    ->setRedirector($app->make(Redirector::class))
                    ->setRequest($app->make('request'));

                if ($form instanceof ValidatesWhenSubmitted && $form->isSubmitted()) {
                    $form->buildForm();
                    $form->validate();
                }
            });
        });
    }
}

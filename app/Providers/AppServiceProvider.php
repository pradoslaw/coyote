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
        Validator::extend('tag', 'Coyote\TagValidator@validateTag');
        Validator::extend('tag_creation', 'Coyote\TagCreationValidator@validateTag');

        Validator::replacer('reputation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        Validator::replacer('tag_creation', function ($message, $attribute, $rule, $parameters) {
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

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\AlertRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\AlertRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\SessionRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\SessionRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\StreamRepositoryInterface',
            'Coyote\\Repositories\\Mongodb\\StreamRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\PmRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\PmRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\WordRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\WordRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\ForumRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\ForumRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\TopicRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\TopicRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\PostRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\PostRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\TagRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\TagRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\Post\\CommentRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\Post\\CommentRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\Post\\VoteRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\Post\\VoteRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\Post\\AcceptRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\Post\\AcceptRepository'
        );
    }
}

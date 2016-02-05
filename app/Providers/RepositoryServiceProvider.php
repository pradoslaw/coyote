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

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\SettingRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\SettingRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\Post\\LogRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\Post\\LogRepository'
        );

        $this->app->bind(
            'Coyote\\Repositories\\Contracts\\Post\\AttachmentRepositoryInterface',
            'Coyote\\Repositories\\Eloquent\\Post\\AttachmentRepository'
        );
    }
}

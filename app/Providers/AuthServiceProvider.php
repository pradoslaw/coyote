<?php

namespace Coyote\Providers;

use Coyote\Forum;
use Coyote\Job;
use Coyote\Firm;
use Coyote\Microblog;
use Coyote\Policies\MicroblogPolicy;
use Coyote\Policies\ForumPolicy;
use Coyote\Policies\PostPolicy;
use Coyote\Policies\JobPolicy;
use Coyote\Policies\FirmPolicy;
use Coyote\Policies\PostCommentPolicy;
use Coyote\Post;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Microblog::class => MicroblogPolicy::class,
        Forum::class => ForumPolicy::class,
        Post::class => PostPolicy::class,
        Post\Comment::class => PostCommentPolicy::class,
        Job::class => JobPolicy::class,
        Firm::class => FirmPolicy::class
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        foreach (['adm-access', 'forum-delete'] as $ability) {
            $gate->define($ability, function ($user) use ($ability) {
                return $user->ability($ability);
            });
        }
    }
}

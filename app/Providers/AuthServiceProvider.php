<?php

namespace Coyote\Providers;

use Coyote\Forum;
use Coyote\Job;
use Coyote\Firm;
use Coyote\Microblog;
use Coyote\Pm;
use Coyote\Policies\MicroblogPolicy;
use Coyote\Policies\ForumPolicy;
use Coyote\Policies\PmPolicy;
use Coyote\Policies\PostPolicy;
use Coyote\Policies\JobPolicy;
use Coyote\Policies\FirmPolicy;
use Coyote\Policies\PostCommentPolicy;
use Coyote\Policies\WikiCommentPolicy;
use Coyote\Post;
use Coyote\User;
use Coyote\Wiki;
use Illuminate\Cache\CacheManager;
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
        Microblog::class        => MicroblogPolicy::class,
        Forum::class            => ForumPolicy::class,
        Post::class             => PostPolicy::class,
        Post\Comment::class     => PostCommentPolicy::class,
        Job::class              => JobPolicy::class,
        Firm::class             => FirmPolicy::class,
        Pm::class               => PmPolicy::class,
        Wiki\Comment::class     => WikiCommentPolicy::class
    ];

    /**
     * Global permissions
     *
     * @var array
     */
    protected $abilities = [
        'adm-access',
        'adm-group',
        'forum-delete',
        'forum-update',
        'forum-lock',
        'forum-move',
        'forum-merge',
        'forum-sticky',
        'job-update',
        'job-delete',
        'firm-update',
        'firm-delete',
        'wiki-admin',
        'pastebin-delete',
        'microblog-update',
        'microblog-delete'
    ];

    /**
     * Users' permissions.
     * A little cache so we don't have to request db/redis every time.
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        foreach ($this->abilities as $ability) {
            $gate->define($ability, function (User $user) use ($ability) {
                $permissions = $this->getUserPermissions($user);

                return $permissions[$ability] ?? false;
            });
        }

        $this->registerPolicies($gate);
    }

    /**
     * @param User $user
     * @return mixed
     */
    private function getUserPermissions(User $user)
    {
        if (isset($this->permissions[$user->id])) {
            return $this->permissions[$user->id];
        }

        // file cache driver does not support tagging.
        if (config('cache.default') !== 'file') {
            $cache = $this->app[CacheManager::class];

            $result = $cache->tags(['permissions'])->rememberForever('permission:' . $user->id, function () use ($user) {
                return $user->getPermissions();
            });
        } else {
            $result = $user->getPermissions();
        }

        return $this->permissions[$user->id] = $result;
    }
}

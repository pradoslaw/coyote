<?php

namespace Coyote\Providers;

use Coyote\Forum;
use Coyote\Microblog;
use Coyote\Policies\MicroblogPolicy;
use Coyote\Policies\ForumPolicy;
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
        Forum::class => ForumPolicy::class
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

        $gate->define('adm-access', function ($user) {
            return $user->ability('adm-access');
        });
    }
}

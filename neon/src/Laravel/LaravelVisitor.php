<?php
namespace Neon\Laravel;

use Coyote\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Application;
use Neon\Domain\Visitor;

readonly class LaravelVisitor implements Visitor
{
    public function __construct(private Application $application)
    {
    }

    public function loggedIn(): bool
    {
        /** @var AuthManager $auth */
        $auth = $this->application->get(AuthManager::class);
        return $auth->check();
    }

    public function loggedInUserAvatarUrl(): ?string
    {
        /** @var AuthManager $auth */
        $auth = $this->application->get(AuthManager::class);
        if ($auth->check()) {
            /** @var User $user */
            $user = $auth->user();
            $url = (string)$user->photo->url();
            return $url ?: null;
        }
        return null;
    }
}

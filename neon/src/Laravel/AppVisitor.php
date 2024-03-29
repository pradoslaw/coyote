<?php
namespace Neon\Laravel;

use Coyote\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Application;
use Neon\Domain\Visitor;

readonly class AppVisitor implements Visitor
{
    public function __construct(private Application $application)
    {
    }

    public function loggedInUserAvatarUrl(): ?string
    {
        /** @var AuthManager $auth */
        $auth = $this->application->get(AuthManager::class);
        if ($auth->check()) {
            /** @var User $user */
            $user = $auth->user();
            return $user->photo->getFilename();
        }
        return null;
    }
}

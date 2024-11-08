<?php
namespace Tests\Unit\Trial\Eloquent;

use Coyote\Feature\Trial\TrialSession;
use Coyote\User;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Forum\ModelsDsl;
use Tests\Unit\BaseFixture\Server\Laravel\Transactional;

class TrialSessionTest extends TestCase
{
    use Transactional;

    private ModelsDsl $dsl;

    #[Before]
    public function initialize(): void
    {
        $this->dsl = new ModelsDsl();
    }

    #[Test]
    public function modelHas_userAsForeignKey(): void
    {
        $id = $this->dsl->newUserReturnId();

        $session = $this->newTrialSession('invited');
        $session->user()->associate($this->userModel($id));
        $session->save();

        /** @var TrialSession $session */
        $session = $this->userModel($id)->trialSession;
        $this->assertSame('invited', $session->stage);
    }

    #[Test]
    public function userHas_optionalTrialSession(): void
    {
        $this->assertNull($this->newUserModel()->trialSession);
    }

    private function newTrialSession(string $stage): TrialSession
    {
        $session = new TrialSession();
        $session->stage = $stage;
        $session->assortment = 'legacy';
        $session->badge_narrow = false;
        return $session;
    }

    private function newUserModel(): User
    {
        $id = $this->dsl->newUserReturnId();
        return $this->userModel($id);
    }

    private function userModel(int $id): User
    {
        return User::query()->findOrFail($id);
    }
}

<?php
namespace Tests\Browser;

use Carbon\Carbon;
use Coyote\User;
use Laravel\Dusk\Browser;

readonly class Driver
{
    public function closeGdprIfVisible(Browser $browser): void
    {
        if ($browser->element('#gdpr-all')->isDisplayed()) {
            $browser
                ->click('#gdpr-all')
                ->waitUntilMissing('.gdpr-modal');
        }
    }

    public function seedUser(
        string $password = null,
        bool   $blocked = false,
        bool   $deleted = false,
    ): User
    {
        return factory(User::class)->create([
            'password'   => bcrypt($password),
            'is_blocked' => $blocked,
            'deleted_at' => $deleted ? Carbon::now() : null,
        ]);
    }
}

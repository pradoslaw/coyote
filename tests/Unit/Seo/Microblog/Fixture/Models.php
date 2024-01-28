<?php
namespace Tests\Unit\Seo\Microblog\Fixture;

use Coyote\Microblog;
use Coyote\User;
use Tests\Unit\BaseFixture\Server\Laravel;
use Tests\Unit\Seo;

trait Models
{
    use Laravel\Transactional;

    function newMicroblog(): int
    {
        $user = new User();
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->save();
        $microblog = new Microblog();
        $microblog->user_id = $user->id;
        $microblog->text = 'irrelevant';
        $microblog->save();
        return $microblog->id;
    }
}

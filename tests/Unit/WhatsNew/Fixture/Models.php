<?php
namespace Tests\Unit\WhatsNew\Fixture;

use Carbon\Carbon;
use Coyote\Microblog;
use Coyote\Tag;
use Coyote\User;
use Tests\Unit\BaseFixture\Server\Laravel;
use Tests\Unit\Seo;

trait Models
{
    use Laravel\Transactional;

    function newWhatsNewItem(string $text, string $dateTime): int
    {
        return $this->newMicroblog(
            $text,
            new Carbon($dateTime),
            $this->newTag('4programmers.net'));
    }

    function newMicroblog(string $text, Carbon $date, Tag $tag): int
    {
        $microblog = new Microblog([
            'user_id' => $this->systemUser()->id,
            'text'    => $text,
        ]);
        $microblog->created_at = $date;
        $microblog->save();
        $microblog->tags()->save($tag);
        return $microblog->id;
    }

    function newTag(string $name): Tag
    {
        $tag = new Tag(['name' => $name]);
        $tag->save();
        return $tag;
    }

    function systemUser(): User
    {
        $user = new User();
        $user->name = '4programmers.net';
        $user->email = 'irrelevant';
        $user->save();
        return $user;
    }
}

<?php
namespace Tests\Unit\Topic\Fixture;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Unit\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newTopicTitle(string $title): Topic
    {
        return $this->storeThread(new Forum, new Topic(['title' => $title]));
    }
}

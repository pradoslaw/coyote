<?php
namespace Tests\Unit\Topic\Fixture;

use Coyote\Topic;
use Tests\Unit\Seo;

trait Models
{
    use Seo\DiscussionForumPosting\Models {
        Seo\DiscussionForumPosting\Models::newTopicTitle as _newTopicTitle;
    }

    function newTopicTitle(string $title): Topic
    {
        return $this->_newTopicTitle($title);
    }
}

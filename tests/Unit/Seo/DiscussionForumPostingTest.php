<?php
namespace Tests\Unit\Seo;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\Seo;

class DiscussionForumPostingTest extends TestCase
{
    use Seo\DiscussionForumPosting\Fixture, BaseFixture\RelativeUri;

    /**
     * @test
     */
    public function id()
    {
        [$schema, $topicId] = $this->schemaForumSlug('apple-forum');
        $this->assertThat(
            $schema['@id'],
            $this->relativeUri("/Forum/apple-forum/$topicId"));
    }

    /**
     * @test
     */
    public function headline()
    {
        $schema = $this->schemaTopicTitle('Banana topic');
        $this->assertSame('Banana topic', $schema['headline']);
    }

    /**
     * @test
     */
    public function replies()
    {
        $schema = $this->schemaForumReplies(replies:7);
        $this->assertThat(
            $schema['interactionStatistic'],
            $this->identicalTo(['@type' => 'InteractionCounter', 'userInteractionCount' => 7]));
    }
}

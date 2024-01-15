<?php
namespace Tests\Unit\Seo;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\Seo;
use TRegx\PhpUnit\DataProviders\DataProvider;

class DiscussionForumPostingTest extends TestCase
{
    use Seo\DiscussionForumPosting\Fixture, BaseFixture\RelativeUri;

    /**
     * @test
     * @dataProvider fields
     */
    public function canonical(string $field)
    {
        [$schema, $topicId] = $this->schemaTopicInForum('Banana topic', 'apple-forum');
        $this->assertThat(
            $schema[$field],
            $this->relativeUri("/Forum/apple-forum/$topicId-banana_topic"));
    }

    public function fields(): DataProvider
    {
        return DataProvider::list('@id', 'url');
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
    public function content()
    {
        $schema = $this->schemaTopicContent('Lorem ipsum');
        $this->assertSame('Lorem ipsum', $schema['text']);
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

    /**
     * @test
     */
    public function authorUser()
    {
        $schema = $this->postingSchema($this->newTopicAuthorUsername('mark'));
        $this->assertThat(
            $schema['author'],
            $this->identicalTo(['@type' => 'Person', 'name' => 'mark']));
    }

    /**
     * @test
     */
    public function authorLegacyGuest()
    {
        $schema = $this->postingSchema($this->newTopicAuthorLegacyGuest('john'));
        $this->assertThat(
            $schema['author'],
            $this->identicalTo(['@type' => 'Person', 'name' => 'john']));
    }
}

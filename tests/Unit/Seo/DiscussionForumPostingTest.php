<?php
namespace Tests\Unit\Seo;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\Seo;
use TRegx\PhpUnit\DataProviders\DataProvider;

class DiscussionForumPostingTest extends TestCase
{
    use Seo\DiscussionForumPosting\Fixture,
        Seo\DiscussionForumPosting\SystemDatabase,
        BaseFixture\RelativeUri;

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
    public function contentMarkdown()
    {
        $schema = $this->schemaTopicContent('Lorem *ipsum*.');
        $this->assertSame('Lorem ipsum.', $schema['text']);
    }

    /**
     * @test
     */
    public function contentHtml()
    {
        $schema = $this->schemaTopicContent('Lorem <b>ipsum</b> &copy;.');
        $this->assertSame('Lorem ipsum ©.', $schema['text']);
    }

    /**
     * @test
     */
    public function contentHtmlEntity()
    {
        $schema = $this->schemaTopicContent('&lt;Lorem&gt;');
        $this->assertSame('<Lorem>', $schema['text']);
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

    /**
     * @test
     */
    public function datePublished()
    {
        $this->systemDatabaseTimezone('America/Los_Angeles');
        $schema = $this->schemaTopicCreatedAt('2016-01-23 11:53:20', timezone:'Europe/Stockholm');
        $this->assertThat($schema['datePublished'],
            $this->identicalTo('2016-01-23T02:53:20-08:00'));
    }
}

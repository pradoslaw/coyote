<?php
namespace Tests\Legacy\IntegrationNew\Topic;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Constraint\ArrayStructure;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;
use Tests\Legacy\IntegrationNew\Topic;
use Tests\Legacy\IntegrationNew\Topic\Fixture\SpaView;

class Test extends TestCase
{
    use Topic\Fixture\Models, Server\Http;

    public function test()
    {
        $topicView = $this->topicView('Orange topic');
        $this->assertThat($topicView,
            new ArrayStructure(['title' => 'Orange topic']));
    }

    private function topicView(string $topicTitle): array
    {
        $topic = $this->newTopicTitle($topicTitle);
        return $this->topicViewVariable("/Forum/{$topic->forum->slug}/$topic->id-{$topic->slug}");
    }

    private function topicViewVariable(string $uri): array
    {
        $view = new SpaView($this->server->get($uri)->assertSuccessful()->content());
        $viewVariables = $view->jsVariables();
        return $viewVariables['topic'];
    }
}

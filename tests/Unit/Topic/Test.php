<?php
namespace Tests\Unit\Topic;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Constraint\ArrayKey;
use Tests\Unit\BaseFixture\Server;
use Tests\Unit\Topic;
use Tests\Unit\Topic\Fixture\SpaView;

class Test extends TestCase
{
    use Topic\Fixture\Models, Server\Http;

    public function test()
    {
        $topicView = $this->topicView('Orange topic');
        $this->assertThat($topicView,
            new ArrayKey('title', $this->identicalTo('Orange topic')));
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

<?php
namespace Tests\Unit\Topic;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server\Laravel;
use Tests\Unit\Seo\Fixture\Constraint\ArrayKey;
use Tests\Unit\Topic;
use Tests\Unit\Topic\Fixture\SpaView;

class Test extends TestCase
{
    use Topic\Fixture\Models, Laravel\Application;

    public function test()
    {
        $topicView = $this->topicView('Orange topic');
        $this->assertThat($topicView,
            new ArrayKey('title', $this->identicalTo('Orange topic')));
    }

    private function topicView(string $topicTitle): array
    {
        $topic = $this->newTopicTitle($topicTitle);
        return $this->topicViewVariable("/Forum/{$topic->forum->slug}/$topic->id");
    }

    private function topicViewVariable(string $uri): array
    {
        $view = new SpaView($this->laravel->get($uri)->assertSuccessful()->content());
        $viewVariables = $view->jsVariables();
        return $viewVariables['topic'];
    }
}

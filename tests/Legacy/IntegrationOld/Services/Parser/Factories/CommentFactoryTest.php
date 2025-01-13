<?php
namespace Tests\Legacy\IntegrationOld\Services\Parser\Factories;

use Coyote\Services\Parser\Factories\CommentFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\IntegrationOld\TestCase;

class CommentFactoryTest extends TestCase
{
    use WithFaker;

    public function testParseCommentWithLineBreaksIsNotAllowed()
    {
        $parser = new CommentFactory($this->app, 0);

        $input = "one\ntwo";
        $this->assertEquals($input, trim($parser->parse($input)));

        $input = "one\n\ntwo";
        $this->assertEquals($input, trim($parser->parse($input)));
    }
}

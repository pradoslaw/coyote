<?php

namespace Tests\Unit\Rules;

use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Rules\InvalidTag;
use Coyote\Tag;
use Tests\TestCase;

class InvalidTagTest extends TestCase
{
    public function testValidationFails()
    {
        $tag = factory(Tag::class)->make(['deleted_at' => now()]);

        $repository = $this->spy(TagRepositoryInterface::class, function ($mock) use ($tag) {
            $mock->shouldReceive('findBy')->andReturn($tag);
        });

        $rule = new InvalidTag($repository);
        $this->assertFalse($rule->passes('', $tag->name));
    }

    public function testValidationPasses()
    {
        $tag = factory(Tag::class)->make();

        $repository = $this->spy(TagRepositoryInterface::class, function ($mock) use ($tag) {
            $mock->shouldReceive('findBy')->andReturn($tag);
        });

        $rule = new InvalidTag($repository);
        $this->assertTrue($rule->passes('', $tag->name));
    }

    public function testValidationPassesTagDoesNotExists()
    {
        $tag = factory(Tag::class)->make();

        $repository = $this->spy(TagRepositoryInterface::class, function ($mock)  {
            $mock->shouldReceive('findBy')->andReturn(null);
        });

        $rule = new InvalidTag($repository);
        $this->assertTrue($rule->passes('', $tag->name));
    }
}

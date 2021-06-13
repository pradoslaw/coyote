<?php

namespace Tests\Unit\Services\TwigBridge\Extensions;

use Coyote\Block as Model;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Coyote\Services\TwigBridge\Extensions;

class BlockTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    public function testShowBlockToAllUsers()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText()]);

        $block = resolve(Extensions\Block::class);
        $block->blocks = collect([$model]);

        $this->assertEquals($content, $block->renderBlock($name));

        $user = factory(User::class)->create();

        Auth::loginUsingId($user->id, true);

        $this->assertEquals($content, $block->renderBlock($name));
    }

    public function testShowAdBlock()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText(), 'max_reputation' => 100]);

        $block = resolve(Extensions\Block::class);
        $block->blocks = collect([$model]);

        $this->assertEquals($content, $block->renderBlock($name));

        $user = factory(User::class)->create();

        Auth::loginUsingId($user->id, true);

        $this->assertEquals($content, $block->renderBlock($name));
    }

    public function testHideAdBlockForPrivilegeUser()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText(), 'max_reputation' => 100]);

        $block = resolve(Extensions\Block::class);
        $block->blocks = collect([$model]);

        $user = factory(User::class)->create(['reputation' => 101]);

        Auth::loginUsingId($user->id, true);

        $this->assertEquals('', $block->renderBlock($name));
    }

    public function testHideAdBlockForSponsor()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText(), 'enable_sponsor' => false]);

        $block = resolve(Extensions\Block::class);
        $block->blocks = collect([$model]);

        $user = factory(User::class)->create(['is_sponsor' => true]);

        Auth::loginUsingId($user->id, true);

        $this->assertEquals('', $block->renderBlock($name));
    }

    public function testShowAdBlockForRegularUser()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText(), 'enable_sponsor' => false]);

        $block = resolve(Extensions\Block::class);
        $block->blocks = collect([$model]);

        $this->assertEquals($content, $block->renderBlock($name));

        $user = factory(User::class)->create();

        Auth::loginUsingId($user->id, true);

        $this->assertEquals($content, $block->renderBlock($name));
    }
}

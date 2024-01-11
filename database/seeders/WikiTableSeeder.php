<?php
namespace Database\Seeders;

use Coyote\User;
use Coyote\Wiki\Page;
use Illuminate\Database\Seeder;

class WikiTableSeeder extends Seeder
{
    public function run(): void
    {
        /** @var User $creator */
        $creator = User::query()->create([
            'name'  => 'Editor',
            'email' => 'editor@host',
        ]);

        $this->create($creator, 'Blog', 'Blog', 'blog.home');
        $this->create($creator, 'Pomoc', 'Pomoc', 'help.home');
        $this->create($creator, 'Patronat', 'Patronat', 'category');
    }

    private function create(User $user, string $title, string $path, string $template): void
    {
        /** @var Page $wiki */
        $wiki = Page::query()->create(['title' => $title, 'template' => $template]);
        $wiki->logs()->create(['user_id' => $user->id, 'title' => $title, 'ip' => 'localhost', 'browser' => '(none)', 'host' => '(none)']);
        $wiki->paths()->create(['path' => $path]);
    }
}

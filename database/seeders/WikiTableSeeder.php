<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WikiTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->create('Blog', 'Blog', 'blog.home');
        $this->create('Pomoc', 'Pomoc', 'help.home');
        $this->create('Patronat', 'Patronat', 'category');
    }

    private function create($title, $path, $template)
    {
        $wiki = \Coyote\Wiki\Page::create(['title' => $title, 'template' => $template]);
        $wiki->logs()->create(['user_id' => 1, 'title' => $title, 'ip' => 'localhost', 'browser' => '(none)', 'host' => '(none)']);
        $wiki->paths()->create(['path' => $path]);
    }
}

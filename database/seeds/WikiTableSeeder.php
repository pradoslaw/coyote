<?php

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
        $wiki = Coyote\Wiki::create(['title' => 'Blog', 'template' => 'blog.home']);
        $wiki->logs()->create(['user_id' => 1, 'title' => 'Blog', 'ip' => 'localhost', 'browser' => '(none)', 'host' => '(none)']);

        $wiki = Coyote\Wiki::create(['title' => 'Pomoc', 'template' => 'help.home']);
        $wiki->logs()->create(['user_id' => 1, 'title' => 'Pomoc', 'ip' => 'localhost', 'browser' => '(none)', 'host' => '(none)']);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Tag::create(['name' => 'php']);
        \Coyote\Tag::create(['name' => 'python']);
        \Coyote\Tag::create(['name' => 'c']);
        \Coyote\Tag::create(['name' => 'c#']);
        \Coyote\Tag::create(['name' => 'c++']);
        \Coyote\Tag::create(['name' => 'java']);
        \Coyote\Tag::create(['name' => 'ruby']);
        \Coyote\Tag::create(['name' => 'delphi']);
        \Coyote\Tag::create(['name' => 'pascal']);
    }
}

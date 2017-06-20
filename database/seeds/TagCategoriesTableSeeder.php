<?php

use Illuminate\Database\Seeder;

class TagCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Tag\Category::forceCreate(['id' => \Coyote\Tag\Category::LANGUAGE, 'name' => 'Język programowania']);
        \Coyote\Tag\Category::forceCreate(['id' => \Coyote\Tag\Category::DATABASE, 'name' => 'Baza danych']);
        \Coyote\Tag\Category::forceCreate(['id' => \Coyote\Tag\Category::FRAMEWORK, 'name' => 'Framework']);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Coyote\Reputation\Type;
use Coyote\Reputation;

class ReputationTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Type::create(['id' => Reputation::POST_VOTE, 'name' => 'Ocena postu', 'points' => 5]);
        Type::create(['id' => Reputation::POST_ACCEPT, 'name' => 'Akceptacja postu', 'points' => 15]);
        Type::create(['id' => Reputation::MICROBLOG, 'name' => 'Wpis na mikroblogu', 'points' => 1]);
        Type::create(['id' => Reputation::MICROBLOG_VOTE, 'name' => 'Ocena wpisu na mikroblogu', 'points' => 5]);
        Type::create(['id' => Reputation::WIKI_CREATE, 'name' => 'Utworzenie nowej strony', 'points' => 15]);
        Type::create(['id' => Reputation::WIKI_UPDATE, 'name' => 'Edycja strony', 'points' => 15]);
        Type::create(['id' => Reputation::CUSTOM, 'name' => 'Akcja moderatora', 'points' => 1]);
        Type::create(['id' => Reputation::WIKI_RATE, 'name' => 'Ocena strony', 'points' => 5]);
    }
}

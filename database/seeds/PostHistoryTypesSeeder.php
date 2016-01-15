<?php

use Illuminate\Database\Seeder;
use Coyote\Post\History;
use Coyote\Post\History\Type;

class PostHistoryTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Type::create(['id' => History::INITIAL_SUBJECT, 'name' => 'Początkowy tytuł wątku']);
        Type::create(['id' => History::INITIAL_BODY, 'name' => 'Początkowa treść']);
        Type::create(['id' => History::INITIAL_TAGS, 'name' => 'Początkowe tagi']);
        Type::create(['id' => History::EDIT_SUBJECT, 'name' => 'Edycja tytułu']);
        Type::create(['id' => History::EDIT_BODY, 'name' => 'Edycja treści']);
        Type::create(['id' => History::EDIT_TAGS, 'name' => 'Edycja tagów']);
        Type::create(['id' => History::ROLLBACK_SUBJECT, 'name' => 'Przywrócenie tytułu']);
        Type::create(['id' => History::ROLLBACK_BODY, 'name' => 'Przywrócenie treści']);
        Type::create(['id' => History::ROLLBACK_TAGS, 'name' => 'Przywrócenie tagów']);
        Type::create(['id' => History::DELETE, 'name' => 'Usunięcie posta']);
        Type::create(['id' => History::RESTORE, 'name' => 'Przywrócenie posta']);
    }
}

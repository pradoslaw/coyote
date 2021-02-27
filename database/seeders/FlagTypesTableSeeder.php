<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FlagTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Flag\Type::create(['name' => 'Spam', 'description' => 'Promowanie produktu, usługi lub strony WWW.', 'order' => 1]);
        \Coyote\Flag\Type::create(['name' => 'Wulgaryzmy', 'description' => 'Materiał zawiera przekaz, który może zostać uznany za obraźliwy.', 'order' => 2]);
        \Coyote\Flag\Type::create(['name' => 'Off-topic', 'description' => 'Wpis odbiegający od tematu.', 'order' => 3]);
        \Coyote\Flag\Type::create(['name' => 'Nieprawidłowa kategoria', 'description' => 'Wpis nie znajduje się we właściwym miejscu.', 'order' => 4]);
        \Coyote\Flag\Type::create(['name' => 'Próba wyłudzenia gotowca', 'description' => 'Prośba o wykonanie zadania na uczelnie.', 'order' => 5]);
        \Coyote\Flag\Type::create(['name' => 'Inne', 'description' => 'Inny problem, który wymaga interwencji moderatora.', 'order' => 6]);
    }
}

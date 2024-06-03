<?php

namespace Database\Seeders;

use Coyote\Flag\Type;
use Illuminate\Database\Seeder;

class FlagTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        Type::query()->create(['name' => 'Spam', 'description' => 'Promowanie produktu, usługi lub strony WWW.', 'order' => 1]);
        Type::query()->create(['name' => 'Wulgaryzmy', 'description' => 'Materiał zawiera przekaz, który może zostać uznany za obraźliwy.', 'order' => 2]);
        Type::query()->create(['name' => 'Off-Topic', 'description' => 'Wpis odbiegający od tematu.', 'order' => 3]);
        Type::query()->create(['name' => 'Nieprawidłowa kategoria', 'description' => 'Wpis nie znajduje się we właściwym miejscu.', 'order' => 4]);
        Type::query()->create(['name' => 'Próba wyłudzenia gotowca', 'description' => 'Prośba o wykonanie zadania na uczelnie.', 'order' => 5]);
        Type::query()->create(['name' => 'Inne', 'description' => 'Inny problem, który wymaga interwencji moderatora.', 'order' => 6]);
    }
}

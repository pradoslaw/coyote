<?php
namespace Database\Seeders;

use Coyote\Flag\Type;
use Illuminate\Database\Seeder;

class FlagTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->createFlagType(1, 'Spam', 'Promowanie produktu, usługi lub strony WWW.');
        $this->createFlagType(2, 'Wulgaryzmy', 'Materiał zawiera przekaz, który może zostać uznany za obraźliwy.');
        $this->createFlagType(3, 'Off-Topic', 'Wpis odbiegający od tematu.');
        $this->createFlagType(4, 'Nieprawidłowa kategoria', 'Wpis nie znajduje się we właściwym miejscu.');
        $this->createFlagType(5, 'Próba wyłudzenia gotowca', 'Prośba o wykonanie zadania na uczelnie.');
        $this->createFlagType(6, 'Inne', 'Inny problem, który wymaga interwencji moderatora.');
    }

    private function createFlagType(int $order, string $name, string $description): void
    {
        Type::query()->firstOrCreate(['name' => $name, 'description' => $description, 'order' => $order]);
    }
}

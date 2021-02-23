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
        \Coyote\Flag\Type::create(['name' => 'Spam', 'description' => 'Promowanie produktu, usługi lub strony WWW.']);
        \Coyote\Flag\Type::create(['name' => 'Wulgaryzmy', 'description' => 'Materiał zawiera przekaz, który może zostać uznany za obraźliwy.']);
        \Coyote\Flag\Type::create(['name' => 'Off-topic', 'description' => 'Wpis odbiegający od tematu.']);
        \Coyote\Flag\Type::create(['name' => 'Inne', 'description' => 'Inny problem, który wymaga interwencji moderatora.']);
    }
}

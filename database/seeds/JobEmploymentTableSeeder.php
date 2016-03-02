<?php

use Illuminate\Database\Seeder;
use \Coyote\Job\Employment;

class JobEmploymentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Employment::create(['name' => 'Umowa o pracę']);
        Employment::create(['name' => 'Umowa zlecenie']);
        Employment::create(['name' => 'Umowa o dzieło']);
        Employment::create(['name' => 'Kontrakt']);
    }
}

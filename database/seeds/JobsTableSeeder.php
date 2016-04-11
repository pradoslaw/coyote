<?php

use Illuminate\Database\Seeder;

class JobsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'name' => 'job-update',
                'description' => 'Edycja ofert pracy',
                'default' => false
            ],
            [
                'name' => 'job-delete',
                'description' => 'Usuwanie ofert pracy',
                'default' => false
            ],
            [
                'name' => 'firm-update',
                'description' => 'Edycja firm',
                'default' => false
            ],
            [
                'name' => 'firm-delete',
                'description' => 'Usuwanie firm',
                'default' => false
            ],
        ];

        foreach ($permissions as $permission) {
            \DB::table('permissions')->insert($permission);
        }
    }
}

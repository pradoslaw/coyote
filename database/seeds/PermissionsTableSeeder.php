<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = \DB::table('permissions')->insert([
            'name'           => 'adm-access',
            'description'    => 'Dostęp do panelu administracyjnego',
            'default'        => false
        ]);

        \DB::table('group_permissions')->where('permission_id', '=', $id)->update(['value' => true]);
    }
}

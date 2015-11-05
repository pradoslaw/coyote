<?php

use Illuminate\Database\Seeder;

class AclPermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = \DB::table('acl_permissions')->insert([
            'name'           => 'adm-access',
            'description'    => 'Dostęp do panelu administracyjnego',
            'default'        => false
        ]);

        \DB::table('acl_data')->where('permission_id', '=', $id)->update(['value' => true]);
    }
}

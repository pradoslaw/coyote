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

        \DB::table('permissions')->insert([
            'name'           => 'forum-sticky',
            'description'    => 'Zakładanie przyklejonych tematów',
            'default'        => false
        ]);

        \DB::table('permissions')->insert([
            'name'           => 'forum-announcement',
            'description'    => 'Pisanie ogłoszeń',
            'default'        => false
        ]);

        \DB::table('permissions')->insert([
            'name'           => 'forum-delete',
            'description'    => 'Kasowanie wątków i komentarzy',
            'default'        => false
        ]);

        \DB::table('permissions')->insert([
            'name'           => 'forum-update',
            'description'    => 'Edycja postów i komentarzy',
            'default'        => false
        ]);

        \DB::table('permissions')->insert([
            'name'           => 'forum-lock',
            'description'    => 'Blokowanie wątków',
            'default'        => false
        ]);

        \DB::table('permissions')->insert([
            'name'           => 'forum-move',
            'description'    => 'Przenoszenie wątków',
            'default'        => false
        ]);

        \DB::table('permissions')->insert([
            'name'           => 'forum-merge',
            'description'    => 'Łączenie postów',
            'default'        => false
        ]);

        \DB::table('permissions')->insert([
            'name'           => 'wiki-admin',
            'description'    => 'Administracja stronami Wiki',
            'default'        => false
        ]);

        \DB::table('group_permissions')->where('permission_id', '=', $id)->update(['value' => true]);
    }
}

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

        $permissions = [
            [
                'name' => 'microblog-update',
                'description' => 'Edycja wpisów mikrobloga',
                'default' => false
            ],
            [
                'name' => 'microblog-delete',
                'description' => 'Usuwanie wpisów mikrobloga',
                'default' => false
            ],
            [
                'name' => 'forum-sticky',
                'description' => 'Zakładanie przyklejonych tematów',
                'default' => false
            ],
            [
                'name' => 'forum-announcement',
                'description' => 'Pisanie ogłoszeń',
                'default' => false
            ],
            [
                'name' => 'forum-delete',
                'description' => 'Kasowanie wątków i komentarzy',
                'default' => false
            ],
            [
                'name' => 'forum-update',
                'description' => 'Edycja postów i komentarzy',
                'default' => false
            ],
            [
                'name' => 'forum-lock',
                'description' => 'Blokowanie wątków',
                'default' => false
            ],
            [
                'name' => 'forum-move',
                'description' => 'Przenoszenie wątków',
                'default' => false
            ],
            [
                'name' => 'forum-merge',
                'description' => 'Łączenie postów',
                'default' => false
            ],
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

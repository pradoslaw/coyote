<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    use \SchemaBuilder;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->db->table('permissions')->insert([
            'name'           => 'adm-access',
            'description'    => 'Dostęp do panelu administracyjnego',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'adm-group',
            'description'    => 'Edycja grup i ustawień',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'adm-payment',
            'description'    => 'Podgląd faktur i płatności',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'forum-sticky',
            'description'    => 'Zakładanie przyklejonych tematów',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'forum-announcement',
            'description'    => 'Pisanie ogłoszeń',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'forum-delete',
            'description'    => 'Kasowanie wątków i komentarzy',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'forum-update',
            'description'    => 'Edycja postów i komentarzy',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'forum-lock',
            'description'    => 'Blokowanie wątków',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'forum-move',
            'description'    => 'Przenoszenie wątków',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'forum-merge',
            'description'    => 'Łączenie postów',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'forum-emphasis',
            'description'    => 'Operatory ! oraz !! w komentarzach na forum',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'wiki-admin',
            'description'    => 'Administracja stronami Wiki',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name'           => 'pastebin-delete',
            'description'    => 'Usuwanie wpisów z Pastebin',
            'default'        => false
        ]);

        $this->db->table('permissions')->insert([
            'name' => 'job-update',
            'description' => 'Edycja ofert pracy',
            'default' => false
        ]);

        $this->db->table('permissions')->insert([
            'name' => 'job-delete',
            'description' => 'Usuwanie ofert pracy',
            'default' => false
        ]);

        $this->db->table('permissions')->insert([
            'name' => 'firm-update',
            'description' => 'Edycja firm',
            'default' => false
        ]);

        $this->db->table('permissions')->insert([
            'name' => 'firm-delete',
            'description' => 'Usuwanie firm',
            'default' => false
        ]);

        $this->db->table('permissions')->insert([
            'name' => 'guide-delete',
            'description' => 'Usuwanie wpisów z Q&A',
            'default' => false
        ]);

        $this->db->table('permissions')->insert([
            'name' => 'guide-update',
            'description' => 'Edycja wpisów z Q&A',
            'default' => false
        ]);

        $this->db->table('permissions')->insert([
            'name' => 'comment-delete',
            'description' => 'Usuwanie komentarzy',
            'default' => false
        ]);

        $this->db->table('permissions')->insert([
            'name' => 'comment-update',
            'description' => 'Edycja komentarzy',
            'default' => false
        ]);

        $group = \Coyote\Group::where('name', 'Administrator')->first();
        $this->db->table('group_permissions')->where('group_id', '=', $group->id)->update(['value' => true]);
    }
}

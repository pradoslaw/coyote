<?php

use Illuminate\Database\Seeder;

class ForumTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
            'name'           => 'forum-edit',
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

        Coyote\Forum::create([
            'name' => 'Newbie',
            'path' => 'Newbie',
            'section' => 'Podstawy programowania',
            'description' => 'Jeżeli jesteś kompletnym laikiem jeżeli chodzi o programowanie, to jest odpowiednia kategoria dla Ciebie. Tutaj możesz zadawać pytania o podstawy programowania, nie narażając się, że Twój temat zostanie skasowany z powodu niskiego poziomu merytorycznego.'
        ]);

        $parent = Coyote\Forum::create([
            'name' => 'Python',
            'path' => 'Python',
            'description' => 'Forum o Pythonie.'
        ]);

        Coyote\Forum::create([
            'name' => 'Dla początkujących',
            'path' => 'Python/Dla_poczatkujacych',
            'description' => 'Forum o Pythonie dla dla początkujących.',
            'parent_id' => $parent->id
        ]);

        Coyote\Forum::create([
            'name' => 'Dla zaawansowanych',
            'path' => 'Python/Dla_zaawansowanych',
            'description' => 'Forum o Pythonie dla zaawansowanych',
            'parent_id' => $parent->id
        ]);

        Coyote\Forum::create([
            'name' => '4programmers.net',
            'path' => '4programmers',
            'description' => 'Przejdź do 4programmers.net',
            'url' => 'http://4programmers.net',
            'section' => 'Inne'
        ]);
    }
}

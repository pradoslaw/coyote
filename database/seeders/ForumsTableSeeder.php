<?php

namespace Database\Seeders;

use Coyote\Events\ForumSaved;
use Coyote\Forum;
use Illuminate\Database\Seeder;

class ForumsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $row = Forum::create([
            'name' => 'Newbie',
            'slug' => 'Newbie',
            'section' => 'Podstawy programowania',
            'description' => 'Jeżeli jesteś kompletnym laikiem jeżeli chodzi o programowanie, to jest odpowiednia kategoria dla Ciebie. Tutaj możesz zadawać pytania o podstawy programowania, nie narażając się, że Twój temat zostanie skasowany z powodu niskiego poziomu merytorycznego.'
        ]);

        event(new ForumSaved($row));

        $parent = Forum::create([
            'name' => 'Python',
            'slug' => 'Python',
            'description' => 'Forum o Pythonie.',
            'section' => 'Podstawy programowania'
        ]);

        event(new ForumSaved($parent));

        $row = Forum::create([
            'name' => 'Dla początkujących',
            'slug' => 'Python/Dla_poczatkujacych',
            'description' => 'Forum o Pythonie dla dla początkujących.',
            'parent_id' => $parent->id,
            'section' => 'Podkategorie'
        ]);

        event(new ForumSaved($row));

        $row = Forum::create([
            'name' => 'Dla zaawansowanych',
            'slug' => 'Python/Dla_zaawansowanych',
            'description' => 'Forum o Pythonie dla zaawansowanych',
            'parent_id' => $parent->id,
            'section' => 'Podkategorie'
        ]);

        event(new ForumSaved($row));

        $row = Forum::create([
            'name' => 'Off-Topic',
            'slug' => 'Off-Topic',
            'description' => 'Miejsce na dyskusje niepasujące do pozostałych kategorii forum, niekoniecznie związane z programowaniem',
            'section' => 'Inne'
        ]);

        event(new ForumSaved($row));
    }
}

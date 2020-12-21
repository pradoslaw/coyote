<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ForumReasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Forum\Reason::create(['name' => 'Spam', 'description' => 'Treść postu zawiera spam.']);
        \Coyote\Forum\Reason::create(['name' => 'Niepoprawny tytuł wątku', 'description' => 'Aby ułatwić życie użytkownikom forum, prosimy o nadawanie sensownych tematów wątków. Proszę unikać tematów typu "pomoc", "proszę o pomoc", "problem" -- nadawanie sensownych tematów wątków spowoduje, iż Twój temat będzie bardziej atrakcyjny dla użytkowników.

Proszę zapoznać się z zasadami zakładania wątków: http://forum.4programmers.net/Newbie/173798-jak_tytulowac_watki_na_forum']);
        \Coyote\Forum\Reason::create(['name' => 'Odświeżanie starego wątku', 'description' => 'Ten temat jest na tyle stary, iż odświeżanie jego nie przynosi żadnych korzyści.']);
        \Coyote\Forum\Reason::create(['name' => 'Niepoprawna kategoria forum', 'description' => 'Prosimy o rozważne dobieranie kategorii, w której ma zostać umieszczony temat.']);
        \Coyote\Forum\Reason::create(['name' => 'Wulgaryzmy', 'description' => 'Twój post zawierał wulgaryzmy lub/i obelgi. Prosimy o kulturalne zachowanie na forum.']);
        \Coyote\Forum\Reason::create(['name' => 'Wątek umieszczony podwójnie', 'description' => 'Moderator uznał, że ten wątek jest duplikatem innego, już istniejącego wątku. Być może przez przypadek utworzyłeś dwa razy ten sam temat.']);
        \Coyote\Forum\Reason::create(['name' => 'Off-topic', 'description' => 'Post nie związany z tematem wątku. Prosimy nie robić niepotrzebnie zamieszania.']);
        \Coyote\Forum\Reason::create(['name' => 'Na wniosek autora wątku', 'description' => 'Założyciel wątku lub osoba aktualnie go kontrolująca poprosiła o usunięcie/przeniesienie/edycję wątku.']);
    }
}

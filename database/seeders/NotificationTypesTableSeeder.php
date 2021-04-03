<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Coyote\Notification\Type;
use Coyote\Notification;

class NotificationTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Type::unguard();

        Type::create(['id' => Notification::PM,
            'name' => '...nowej wiadomości prywatnej',
            'headline' => 'Nowa wiadomość od: {sender}',
            'profile' => false,
            'email' => true,
        ]);
        Type::create(['id' => Notification::TOPIC_SUBSCRIBER,
            'name' => '...nowych postach w obserwowanych wątkach',
            'headline' => '{sender} dodał odpowiedź w wątku',
            'profile' => true,
            'email' => true,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::TOPIC_MOVE,
            'name' => '...przeniesieniu Twojego wątku',
            'headline' => 'Wątek został przeniesiony',
            'profile' => true,
            'email' => true,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::TOPIC_DELETE,
            'name' => '...usunięciu Twojego wątku',
            'headline' => 'Wątek został usunięty przez {sender}',
            'profile' => true,
            'email' => true,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::POST_DELETE,
            'name' => '...usunięciu Twojego postu',
            'headline' => 'Post został usunięty przez {sender}',
            'profile' => true,
            'email' => true,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::POST_COMMENT,
            'name' => '...nowym komentarzu w Twoim poście',
            'headline' => '{sender} dodał komentarz do postu',
            'profile' => true,
            'email' => true,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::WIKI_SUBSCRIBER,
            'name' => '...zmianie w obserwowanym tekście',
            'headline' => 'Modyfikacja strony',
            'profile' => true,
            'email' => true,
            'category' => 'Kompendium wiedzy'
        ]);
        Type::create(['id' => Notification::WIKI_COMMENT,
            'name' => '...komentarzach w obserwowanych artykułach',
            'headline' => '{sender} dodał komentarz',
            'profile' => true,
            'email' => true,
            'category' => 'Kompendium wiedzy'
        ]);
        Type::create(['id' => Notification::POST_EDIT,
            'name' => '...modyfikacji Twojego postu (lub postu, który obserwujesz)',
            'headline' => '{sender} zmodyfikował post',
            'profile' => true,
            'email' => true,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::TOPIC_SUBJECT,
            'name' => '...zmianie tytułu wątku na forum',
            'headline' => 'Tytuł wątku został zmieniony',
            'profile' => true,
            'email' => false,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::POST_ACCEPT,
            'name' => 'Powiadamiaj jeżeli Twoja odpowiedź zostanie zaakceptowana przez autora wątku',
            'headline' => 'Twoja odpowiedź została zaakceptowana',
            'profile' => true,
            'email' => false,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::POST_COMMENT_LOGIN,
            'name' => 'Powiadamiaj jeżeli ktoś wspomni o Tobie w komentarzu na forum',
            'headline' => '{sender} wspomniał o Tobie w komentarzu na forum',
            'profile' => true,
            'email' => false,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::POST_LOGIN,
            'name' => '...wystąpieniu Twojego loginu w postach na forum',
            'headline' => '{sender} wspomniał o Tobie w poście na forum',
            'profile' => true,
            'email' => false,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::MICROBLOG_LOGIN,
            'name' => '...wystąpieniu Twojego loginu w wiadomościach na mikroblogu',
            'headline' => '{sender} wspomniał o Tobie we wpisie mikroblogu',
            'profile' => true,
            'email' => false,
            'category' => 'Mikroblogi'
        ]);
        Type::create(['id' => Notification::POST_VOTE,
            'name' => '...ocenie Twojego postu na forum',
            'headline' => '{sender} docenił Twój post',
            'profile' => true,
            'email' => false,
            'category' => 'Forum'
        ]);
        Type::create(['id' => Notification::MICROBLOG_VOTE,
            'name' => '...ocenie Twojego wpisu na mikroblogu',
            'headline' => '{sender} docenił Twój wpis na mikroblogu',
            'profile' => true,
            'email' => false,
            'category' => 'Mikroblogi'
        ]);
        Type::updateOrCreate(['id' => Notification::MICROBLOG_SUBSCRIBER,
            'name' => '...wpisie obserwowanego użytkownika na mikroblogu',
            'headline' => '{sender} dodał wpis na mikroblogu',
            'profile' => true,
            'email' => false,
            'category' => 'Mikroblogi'
        ]);
        Type::create(['id' => Notification::MICROBLOG_COMMENT,
            'name' => '...nowym komentarzu do obserwowanego wpisu',
            'headline' => '{sender} dodał komentarz na mikroblogu',
            'profile' => true,
            'email' => false,
            'category' => 'Mikroblogi'
        ]);
        Type::forceCreate(['id' => Notification::FLAG,
            'name' => '...o nowym raporcie',
            'headline' => '{sender} dodał nowy raport',
            'profile' => true,
            'email' => true,
            'is_public' => false
        ]);
        Type::forceCreate(['id' => Notification::JOB_CREATE,
            'name' => '...o dodanej ofercie pracy',
            'headline' => 'Dodano nową ofertę pracy',
            'profile' => true,
            'email' => true,
            'is_public' => false
        ]);
        Type::forceCreate(['id' => Notification::JOB_COMMENT,
            'name' => '...o komentarzu w obserwowanej ofercie pracy',
            'headline' => 'Dodano komentarz do obserwowanej oferty pracy',
            'category' => 'Praca',
            'profile' => true,
            'email' => true,
            'is_public' => true
        ]);
        Type::forceCreate(['id' => Notification::JOB_APPLICATION,
            'name' => '...o aplikacji w ofercie pracy',
            'headline' => '{sender} wysłał aplikacje w ogłoszeniu',
            'category' => 'Praca',
            'profile' => true,
            'email' => true,
            'is_public' => false
        ]);
        Type::updateOrCreate(['id' => Notification::MICROBLOG_DELETE], [
            'name' => '...usunięciu Twojego wpisu',
            'headline' => 'Wpis został usunięty przez {sender}',
            'profile' => true,
            'email' => true,
            'category' => 'Mikroblogi'
        ]);
        Type::updateOrCreate(['id' => Notification::POST_COMMENT_MIGRATED], [
            'name' => '...zamianie komentarza na post',
            'headline' => 'Komentarz został zamieniony na post przez {sender}',
            'profile' => true,
            'email' => true,
            'category' => 'Forum'
        ]);
    }
}

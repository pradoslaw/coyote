<?php

use Illuminate\Database\Seeder;
use Coyote\Alert\Type;
use Coyote\Alert;

class AlertTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Type::unguard();

        Type::create(['id'       => Alert::PM,
                      'name'     => 'Powiadamiaj o nowej wiadomości prywatnej',
                      'headline' => 'Nowa wiadomość od: {sender}',
                      'profile'  => false,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::TOPIC_SUBSCRIBER,
                      'name'     => 'Powiadamiaj o nowych postach w obserwowanych wątkach',
                      'headline' => '{sender} dodał odpowiedź w wątku',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::TOPIC_MOVE,
                      'name'     => 'Powiadamiaj o przeniesieniu Twojego wątku',
                      'headline' => 'Wątek został przeniesiony',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::TOPIC_DELETE,
                      'name'     => 'Powiadamiaj o usunięciu Twojego wątku',
                      'headline' => 'Wątek został usunięty przez {sender}',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::POST_DELETE,
                      'name'     => 'Powiadamiaj o usunięciu Twojego postu',
                      'headline' => 'Post został usunięty przez {sender}',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::POST_SUBSCRIBER,
                      'name'     => 'Powiadamiaj o nowym komentarzu w Twoim poście',
                      'headline' => '{sender} dodał komentarz do postu',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::PAGE,
                      'name'     => 'Powiadamiaj o zmianie w obserwowanym tekście',
                      'headline' => 'Modyfikacja strony',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::COMMENT,
                      'name'     => 'Powiadamiaj o komentarzach w obserwowanych artykułach',
                      'headline' => '{sender} dodał komentarz',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::POST_EDIT,
                      'name'     => 'Powiadamiaj o modyfikacji Twojego postu (lub postu, który obserwujesz)',
                      'headline' => '{sender} zmodyfikował post',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::TOPIC_SUBJECT,
                      'name'     => 'Powiadamiaj o zmianie tytułu wątku na forum',
                      'headline' => 'Tytuł wątku został zmieniony',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::POST_ACCEPT,
                      'name'     => 'Powiadamiaj jeżeli Twoja odpowiedź zostanie zaakceptowana przez autora wątku',
                      'headline' => 'Twoja odpowiedź została zaakceptowana',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::POST_COMMENT_LOGIN,
                      'name'     => 'Powiadamiaj jeżeli ktoś wspomni o Tobie w komentarzu na forum',
                      'headline' => '{sender} wspomniał o Tobie w komentarzu na forum',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::POST_LOGIN,
                      'name'     => 'Powiadamiaj o wystąpieniu Twojego loginu w postach na forum',
                      'headline' => '{sender} wspomniał o Tobie w poście na forum',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::MICROBLOG,
                      'name'     => 'Powiadamiaj o odpowiedzi na wpis na mikroblogu',
                      'headline' => '{sender} odpisał na Twój wpis na mikroblogu',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::MICROBLOG_LOGIN,
                      'name'     => 'Powiadamiaj o wystąpieniu Twojego loginu w wiadomościach na mikroblogu',
                      'headline' => '{sender} wspomniał o Tobie we wpisie mikroblogu',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::POST_VOTE,
                      'name'     => 'Powiadamiaj o ocenie Twojego postu na forum',
                      'headline' => '{sender} docenił Twój post',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::MICROBLOG_VOTE,
                      'name'     => 'Powiadamiaj o ocenie Twojego wpisu na mikroblogu',
                      'headline' => '{sender} docenił Twój wpis na mikroblogu',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::MICROBLOG_SUBSCRIBER,
                      'name'     => 'Powiadamiaj o nowym komentarzu do wpisu, który doceniłeś',
                      'headline' => '{sender} dodał komentarz do wpisu, który doceniłeś',
                      'profile'  => true,
                      'email'    => false
        ]);
    }
}

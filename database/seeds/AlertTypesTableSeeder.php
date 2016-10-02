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
                      'name'     => '...nowej wiadomości prywatnej',
                      'headline' => 'Nowa wiadomość od: {sender}',
                      'profile'  => false,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::TOPIC_SUBSCRIBER,
                      'name'     => '...nowych postach w obserwowanych wątkach',
                      'headline' => '{sender} dodał odpowiedź w wątku',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::TOPIC_MOVE,
                      'name'     => '...przeniesieniu Twojego wątku',
                      'headline' => 'Wątek został przeniesiony',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::TOPIC_DELETE,
                      'name'     => '...usunięciu Twojego wątku',
                      'headline' => 'Wątek został usunięty przez {sender}',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::POST_DELETE,
                      'name'     => '...usunięciu Twojego postu',
                      'headline' => 'Post został usunięty przez {sender}',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::POST_SUBSCRIBER,
                      'name'     => '...nowym komentarzu w Twoim poście',
                      'headline' => '{sender} dodał komentarz do postu',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::WIKI_SUBSCRIBER,
                      'name'     => '...zmianie w obserwowanym tekście',
                      'headline' => 'Modyfikacja strony',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::WIKI_COMMENT,
                      'name'     => '...komentarzach w obserwowanych artykułach',
                      'headline' => '{sender} dodał komentarz',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::POST_EDIT,
                      'name'     => '...modyfikacji Twojego postu (lub postu, który obserwujesz)',
                      'headline' => '{sender} zmodyfikował post',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::TOPIC_SUBJECT,
                      'name'     => '...zmianie tytułu wątku na forum',
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
                      'name'     => '...wystąpieniu Twojego loginu w postach na forum',
                      'headline' => '{sender} wspomniał o Tobie w poście na forum',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::MICROBLOG,
                      'name'     => '...odpowiedzi na wpis na mikroblogu',
                      'headline' => '{sender} odpisał na Twój wpis na mikroblogu',
                      'profile'  => true,
                      'email'    => true
        ]);
        Type::create(['id'       => Alert::MICROBLOG_LOGIN,
                      'name'     => '...wystąpieniu Twojego loginu w wiadomościach na mikroblogu',
                      'headline' => '{sender} wspomniał o Tobie we wpisie mikroblogu',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::POST_VOTE,
                      'name'     => '...ocenie Twojego postu na forum',
                      'headline' => '{sender} docenił Twój post',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::MICROBLOG_VOTE,
                      'name'     => '...ocenie Twojego wpisu na mikroblogu',
                      'headline' => '{sender} docenił Twój wpis na mikroblogu',
                      'profile'  => true,
                      'email'    => false
        ]);
        Type::create(['id'       => Alert::MICROBLOG_SUBSCRIBER,
                      'name'     => '...nowym komentarzu do wpisu, który doceniłeś',
                      'headline' => '{sender} dodał komentarz do wpisu, który doceniłeś',
                      'profile'  => true,
                      'email'    => false
        ]);
    }
}

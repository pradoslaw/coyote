<?php

return [

    // verbs
    'create' => 'dodał',
    'delete' => 'usunał',
    'restore' => 'przywrócił',
    'update' => 'zmienił',
    'vote' => 'zagłosował',
    'accept' => 'zaakceptował',
    'reject' => 'zrezygnował z akceptacji',
    'move' => 'przeniósł',
    'rollback' => 'przywrócił',
    'lock' => 'zablokował',
    'unlock' => 'odblokował',
    'copy' => 'skopiował',
    'login' => 'zalogował się',
    'logout' => 'wylogował się',
    'unlink' => 'usunął kopie',
    'merge' => 'połączył',
    'confirm' => 'potwierdził',

    // objects
    'microblog' => 'wpis',
    'comment' => 'komentarz',
    'post' => 'post',
    'topic' => 'wątek',
    'job' => 'ogłoszenie',
    'pastebin' => 'pastebin',
    'wiki' => 'artykuł',
    'flag' => 'raport',
    'firewall' => 'ban',
    'person' => 'profil',
    'block' => 'blok',

    'headline' => [
        'microblog'         => ':actor :verb :object na mikroblogu',
        'comment'           => ':actor :verb :object w :target',
        'topic'             => ':actor :verb wątek :object',
        'post'              => ':actor :verb :object w :target',
        'accept'            => ':actor :verb :object w :target',
        'topic:move'        => ':actor :verb :object z :source do :target',
        'job'               => ':actor :verb :object o pracę: :offer',
        'flag'              => ':actor :verb :object',
        'wiki'              => ':actor :verb :object :title',
        'person'            => ':actor :verb :object',
        'person:confirm'    => ':actor potwierdził adres e-mail',
        'firewall'          => ':actor :verb :object',
        'object'            => ':actor :verb',
        'object:throttle'   => 'Nieudane logowanie na konto :login',
        'block'             => ':actor :verb :object',
    ]
];

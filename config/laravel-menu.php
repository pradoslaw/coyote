<?php

return [
    'settings' => [
        'default'          => [
            'auto_activate'    => true,
            'activate_parents' => true,
            'active_class'     => 'active',
            'restful'          => false,
            'cascade_data'     => true,
            'rest_base'        => '',      // string|array
            'active_element'   => 'link',  // item|link
        ],
        '__master_menu___' => [
            'restful' => true,
        ],
        '_forum'           => [
            'auto_activate' => false,
            'restful'       => true,
        ],
    ],

    '__master_menu___' => [
        'Forum'      => ['route' => 'forum.home', 'class' => 'nav-item dropdown'],
        'Mikroblogi' => ['route' => 'microblog.home', 'class' => 'nav-item'],
        'Praca'      => ['route' => 'job.home', 'class' => 'nav-item'],
        'Kompendium' => [
            'url'      => 'Kategorie',
            'class'    => 'nav-item dropdown',
            'children' => [
                'Delphi'       => ['url' => 'Delphi'],
                'C/C++'        => ['url' => 'C'],
                'C#'           => ['url' => 'C_sharp'],
                'Python'       => ['url' => 'Python'],
                'Java'         => ['url' => 'Java'],
                'Turbo Pascal' => ['url' => 'Turbo_Pascal'],
                'Z pogranicza' => ['url' => 'Z_pogranicza'],
                'Assembler'    => ['url' => 'Assembler'],
                'Algorytmy'    => ['url' => 'Algorytmy'],
                '(X)HTML'      => ['url' => '(X)HTML'],
                'CSS'          => ['url' => 'CSS'],
            ],
        ],
    ],

    // _ na poczatku gdyz ten plugin korzysta z metody share() klasy View, a nazwa "forum" moze
    // wchodzic w konflikt z innymi zmiennymi przekazywanymi do twiga
    '_forum'           => [
        'Kategorie'   => ['route' => 'forum.categories', 'class' => 'nav-item'],
        'Wszystkie'   => ['route' => 'forum.all', 'class' => 'nav-item'],
        'Obserwowane' => ['route' => 'forum.subscribes', 'class' => 'nav-item', 'data' => ['role' => true]],
        'Moje'        => ['route' => 'forum.mine', 'class' => 'nav-item', 'data' => ['role' => true], 'title' => 'Wątki w których brałem udział'],
        'Moje tagi'   => ['route' => 'forum.interesting', 'class' => 'nav-item', 'title' => 'Wątki zawierające moje tagi'],
    ],
];

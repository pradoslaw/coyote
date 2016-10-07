<?php

return [
    'settings' => [
        'default' => [
            'auto_activate'    => true,
            'activate_parents' => true,
            'active_class'     => 'active',
            'restful'          => false,
            'cascade_data'     => true,
            'rest_base'        => '',      // string|array
            'active_element'   => 'item',  // item|link
        ]
    ],
    'master' => [
        'Forum' => ['route' => 'forum.home'],
        'Mikroblogi' => ['route' => 'microblog.home'],
        'Praca' => ['route' => 'job.home'],
        'Pastebin' => ['route' => 'pastebin.show'],
        'Kompendium' => [
            'url' => 'Kategorie',
            'children' => [
                'Delphi' => ['url' => 'Delphi'],
                'C/C++' => ['url' => 'C'],
                'C#' => ['url' => 'C_sharp'],
                'Python' => ['url' => 'Python'],
                'Java' => ['url' => 'Java'],
                'Turbo Pascal' => ['url' => 'Turbo_Pascal'],
                'Z pogranicza' => ['url' => 'Z_pogranicza'],
                'Assembler' => ['url' => 'Assembler'],
                'Algorytmy' => ['url' => 'Algorytmy'],
                '(X)HTML' => ['url' => '(X)HTML'],
                'CSS' => ['url' => 'CSS']
            ]
        ]
    ]
];

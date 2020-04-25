(function ($) {
    "use strict";

    $.fn.draft = function () {
        if (typeof localStorage !== 'undefined') {
            var $this = $(this);

            var $textarea = $('textarea[name="text"]', this);
            var $username = $('input[name="user_name"]', this);

            var dataIndex = 'post-' + $this.attr('action');

            if (localStorage.getItem(dataIndex) && !$textarea.val().length) {
                $textarea.val($.parseJSON(localStorage.getItem(dataIndex)).content);
            }

            try {
                for (var item in localStorage) {
                    if (localStorage.hasOwnProperty(item)) {
                        if (item.substr(0, 4) === 'post') {
                            var time = new Date().getTime() / 1000;

                            if ($.parseJSON(localStorage.getItem(item)).timestamp < time - 3600) {
                                localStorage.removeItem(item);
                            }
                        }
                    }
                }
            }
            catch (e) {
            }

            $this.submit(function () {
                localStorage.removeItem(dataIndex);
                $textarea.unbind('keyup');
            });

            $textarea.keyup(function () {
                try {
                    localStorage.setItem(dataIndex, JSON.stringify({
                        'content': $textarea.val(),
                        'timestamp': new Date().getTime() / 1000
                    }));
                }
                catch (e) {
                    localStorage.clear();
                }
            });

            $username.keyup(function () {
                try {
                    localStorage.setItem('user-name', $(this).val());
                }
                catch (e) {
                }
            });

            // jezeli istnieje pole "Autor", nie jset ono puste: to warunki wykonania tego kodu
            if ($username.length > 0 && !$username.val().length) {
                if (localStorage.getItem('user-name')) {
                    $username.val(localStorage.getItem('user-name'));
                }
                else {
                    // taki maly bonus ;) Generowanie losowych nickow dla anonimowy
                    var adjectives = [
                        'Świetny',
                        'Zimny',
                        'Krwawy',
                        'Pijany',
                        'Czarny',
                        'Biały',
                        'Wielki',
                        'Mały',
                        'Złoty',
                        'Mistrzowski',
                        'Krzywy',
                        'Zakręcony',
                        'Trzeźwy',
                        'Skromny',
                        'Nadziany',
                        'Bogaty',
                        'Uczynny',
                        'Chory',
                        'Szalony',
                        'Błękitny',
                        'Brunatny',
                        'Smutny',
                        'Wesoły',
                        'Nieposkromiony'
                    ];

                    var nouns = [
                        'Młot',
                        'Kaczor',
                        'Orzeł',
                        'Lew',
                        'Kot',
                        'Szczur',
                        'Pomidor',
                        'Krawiec',
                        'Terrorysta',
                        'Samiec',
                        'Mleczarz',
                        'Kret',
                        'Karp',
                        'Jeleń',
                        'Kura',
                        'Wąż',
                        'Ogórek',
                        'Programista',
                        'Szewc',
                        'Polityk',
                        'Rycerz',
                        'Ogrodnik'
                    ];

                    $username.val(adjectives[parseInt(Math.random() * adjectives.length)] + ' ' + nouns[parseInt(Math.random() * nouns.length)]).trigger('keyup');
                }
            }
        }
    };
})(jQuery);

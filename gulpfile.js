var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {

    var base = [
        '../bower/jquery/dist/jquery.min.js',
        '../bower/bootstrap/js/tooltip.js',
        '../bower/bootstrap/js/dropdown.js',// menu jest na kazdej stronie (dla uzytkownikow zalogowanych)
        '../bower/bootstrap/js/collapse.js', // to musi sie znajdowac na kazdej podstronie (zwijanie menu dla urzadzen mobilnych)
        'components/breadcrumb.js',
        'components/realtime.js',
        'components/state.js',
        'components/declination.js',
        'components/date.js',
        'components/notifications.js',
        'components/session.js',
        'components/vcard.js',
        'components/popover.js',
        'components/flag.js',
        'main.js'
    ];

    mix.babel(['pages/job/submit.js'], 'public/js/job-submit.js')
        .babel(['pages/job/tinymce.js'], 'public/js/job-tinymce.js')
        .babel(['pages/job.js', '../bower/bootstrap/js/modal.js', 'components/subscribe.js'], 'public/js/job.js')
        .babel(['pages/wiki.js', '../bower/bootstrap/js/modal.js', 'components/subscribe.js'], 'public/js/wiki.js')
        .babel(['components/geo-ip.js'], 'public/js/geo-ip.js');

    mix.scripts(base, 'public/js/main.js')
        /*
         | Forum (popover jest na forum potrzebne do pokazywania okna z ktorego mozna skopiowac skrocony link do postu)
         */
        .scripts([
            '../bower/jquery-color-animation/jquery.animate-colors-min.js',
            '../bower/bootstrap/js/modal.js',
            '../bower/bootstrap/js/tab.js',
            '../bower/bootstrap/js/popover.js',
            'pages/forum/draft.js',
            'pages/forum/tags.js',
            'pages/forum/sidebar.js',
            'pages/forum/posting.js'
        ], 'public/js/forum.js')

        /*
         | Mikroblogu
         */
        .scripts([
            '../bower/jquery-color-animation/jquery.animate-colors-min.js',
            '../bower/ekko-lightbox/dist/ekko-lightbox.min.js',
            'pages/microblog.js'
        ], 'public/js/microblog.js')

        /*
         | Komponent uzywany przy publikowaniu tekstu. laczy ze soba pluginy, np. dynamicznie zmieniajace
         | rozmiar pola textarea, czy tez podpowiadajacy login uzytkownika w tekscie
         */
        .scripts(['components/prompt.js', 'components/autogrow.js', 'components/fast-submit.js', 'components/upload.js', 'components/input-focus.js'], 'public/js/posting.js')

        /*
         | JS do prostego edytora markdown
         */
        .scripts(['components/wikieditor.js'], 'public/js/wikieditor.js')

        /*
         | auto complete. uzywany m.in. w podczas pisania wiadomosci, czy tez ustalania umiejetnosci
         */
        .scripts(['components/auto-complete.js'], 'public/js/auto-complete.js')

        /*
         | Komponent z mozliwoscia wyboru tagow
         */
        .scripts(['components/tags.js'], 'public/js/tags.js')

        /*
         | Uzywane na niewielu stronach. tam gdzie trzeba przelaczac sie miedzy zakladkami
         */
        .scripts(['../bower/bootstrap/js/tab.js'], 'public/js/tab.js')

        /*
         | Okna modalne, tj. wyswietlanie komunikatow - np. zapytanie czy na pewno usunac post
         */
        .scripts(['../bower/bootstrap/js/modal.js'], 'public/js/modal.js')

        /*
         | Scrollbar uzywany m.in w oknie powiadomien, wiadomosci prywatnych czy tez na stronie glownej
         | gdzie wyswietlane sa ostatnie aktywnosci z forum
         */
        .scripts(['../bower/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js'], 'public/js/perfect-scrollbar.js')

        /*
         | Plugin animujacy tlo danego elementu strony (mikroblog, post) dla usprawnienia ubsability
         */
        .scripts(['../bower/jquery-color-animation/jquery.animate-colors-min.js'], 'public/js/animate-colors.js')

        /*
         | jQuery Ui w okrojonej wersji.
         | ---------------------------------------
         | Wykorzystywany m.in. po zaladowaniu okna powiadomien (mamy mozliwosc zmiany rozmiaru tego okna)
         | oraz w panelu uzytkownika na stronie umiejetnosci gdzie mamy mozliwosc dodawania/usuwania umiejetnosci
         | a takze ich przesuwania wzgledem siebie (za pomoca drag'n'drop)
         */
        .scripts([
            '../bower/jquery-ui/ui/minified/core.min.js',
            '../bower/jquery-ui/ui/minified/widget.min.js',
            '../bower/jquery-ui/ui/minified/mouse.min.js',
            '../bower/jquery-ui/ui/minified/resizable.min.js',
            '../bower/jquery-ui/ui/minified/sortable.min.js'
        ], 'public/js/jquery-ui.js')

        .scripts(['../bower/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js'], 'public/js/datepicker.js')
        .scripts(['../bower/pretty-text-diff/jquery.pretty-text-diff.js'], 'public/js/diff.js');

    mix.sass('main.scss')
        .sass('pages/auth.scss')
        .sass('pages/homepage.scss')
        .sass('pages/microblog.scss')
        .sass('pages/forum.scss')
        .sass('pages/wiki.scss')
        .sass('pages/help.scss')
        .sass('pages/user.scss')
        .sass('pages/profile.scss')
        .sass('pages/job.scss')
        .sass('pages/errors.scss')
        .sass('pages/adm.scss')
        .sass('pages/pastebin.scss')
        .sass('pages/search.scss');

    mix.copy('resources/assets/bower/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css', 'public/css/datepicker.css');

    // versioning only on production server
    if (elixir.config.production) {
        mix.version([
            'js/main.js',
            'js/microblog.js',
            'js/forum.js',
            'js/job.js',
            'js/job-submit.js',
            'js/job-tinymce.js',
            'js/posting.js',
            'js/wikieditor.js',
            'js/auto-complete.js',
            'js/tags.js',
            'js/tab.js',
            'js/modal.js',
            'js/perfect-scrollbar.js',
            'js/animate-colors.js',
            'js/jquery-ui.js',
            'js/wiki.js',
            'js/diff.js',
            'js/geo-ip.js',

            'css/main.css',
            'css/auth.css',
            'css/homepage.css',
            'css/microblog.css',
            'css/forum.css',
            'css/wiki.css',
            'css/user.css',
            'css/profile.css',
            'css/job.css',
            'css/errors.css',
            'css/pastebin.css',
            'css/adm.css',
            'css/help.css',
            'css/search.css'
        ]);
    }
});


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

elixir(function(mix) {

    var base = [
        'jquery-last.min.js',
        'bootstrap/tooltip.js',
        'bootstrap/modal.js',
        'bootstrap/dropdown.js',
        'bootstrap/collapse.js',
        'components/declination.js',
        'components/date.js',
        'components/notifications.js',
        'components/session.js',
        'components/vcard.js',
        'main.js'
    ];

    mix.scripts(base, 'public/js/main.js')
       .scripts(['bootstrap/tab.js', 'vendor/perfect-scrollbar.js'], 'public/js/homepage.js')
       .scripts(['pages/forum.js'], 'public/js/forum.js')
       .scripts(['pages/microblog.js', 'components/prompt.js', 'components/autogrow.js', 'components/fast-submit.js'], 'public/js/microblog.js')
       .scripts(['wikieditor/jquery.wikieditor.js', 'wikieditor/wikieditor.toolbar.js'], 'public/js/jquery.wikieditor.js')
       .scripts(['bootstrap/popover.js'], 'public/js/popover.js')
       .scripts(['components/prompt.js'], 'public/js/prompt.js')
       .scripts(['components/autogrow.js'], 'public/js/autogrow.js')
       .scripts(['components/fast-submit.js'], 'public/js/fast-submit.js')
       .scripts(['vendor/perfect-scrollbar.js'], 'public/js/perfect-scrollbar.js');

    mix.sass('main.scss')
       .sass('pages/auth.scss')
       .sass(['pages/homepage.scss', 'vendor/perfect-scrollbar.scss'], 'public/css/homepage.css')
       .sass('pages/microblog.scss')
       .sass('pages/forum.scss')
       .sass('pages/wiki.scss')
       .sass('pages/user.scss')
       .sass('pages/job.scss')
       .sass('vendor/wikieditor.scss');
});


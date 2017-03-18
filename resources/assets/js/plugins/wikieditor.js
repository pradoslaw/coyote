import Textarea from '../libs/textarea';

(function ($) {
    'use strict';

    let languages = {
        'actionscript': 'ActionScript',
        'ada': 'ADA',
        'asm': 'Asm',
        'autoit': 'AutoIT',
        'bash': 'Bash',
        'bf': 'BrainFuck',
        'c': 'C',
        'cpp': 'C++',
        'csharp': 'C#',
        'clojure': 'Clojure',
        'cobol': 'COBOL',
        'css': 'CSS',
        'd': 'D',
        'delphi': 'Delphi',
        'diff': 'Diff',
        'fsharp': 'F#',
        'fortan': 'Fortran',
        'groovy': 'Groovy',
        'haskell': 'Haskell',
        'html': 'HTML',
        'ini': 'INI',
        'inno': 'Inno Setup',
        'java': 'Java',
        'javascript': 'JavaScript',
        'jquery': 'JQuery',
        'latex': 'LaTeX',
        'lisp': 'Lisp',
        'lua': 'Lua',
        'matlab': 'Matlab',
        'ocaml': 'OCaml',
        'pascal': 'Pascal',
        'perl': 'Perl',
        'php': 'PHP',
        'plsql': 'PL/SQL',
        'powershell': 'PowerShell',
        'prolog': 'Prolog',
        'python': 'Python',
        'pcre': 'RegEx',
        'ruby': 'Ruby',
        'scala': 'Scala',
        'scheme': 'Scheme',
        'smalltalk': 'Smalltalk',
        'sql': 'SQL',
        'vb': 'Visual Basic',
        'vbnet': 'Visual Basic .NET',
        'winbatch': 'Windows Bat',
        'reg': 'Windows Registry',
        'xml': 'XML'
    };

    $.fn.wikiEditor = function () {
        return this.each(function () {
            let textarea    = $(this);
            let el          = new Textarea(textarea[0]);
            let toolbar     = $('#wiki-toolbar');
            let select      = $('.select-menu-wrapper', toolbar).find('ul');

            $.each(languages, function(key, value) {
                select.append('<li><a data-open="```' + key + '<br>" data-close="<br>```" title="Kod źródłowy: ' + value + '">' + value + '</a></li>');
            });

            $('a[data-open], button[data-open]', toolbar).click(function() {
                el.insertAtCaret($(this).data('open').replace(/<br>/g, "\n"), $(this).data('close').replace(/<br>/g, "\n"), el.isSelected() ? el.getSelection() : '');
            });

            $(textarea).bind('keydown', function(e) {
                if ((e.which === 9 || e.keyCode === 9) && e.shiftKey) {
                    el.insertAtCaret("\t", '', "");

                    return false;
                }
            });

            $('.btn-quote', toolbar).click(() => {
                el.insertAtCaret('> ', '', el.getSelection().replace(/\r\n/g, "\n").replace(/\n/g, "\n> "));
            });

            $('.select-menu-search input', toolbar).keyup(function(e) {
                let searchText = $.trim($(this).val().toLowerCase());

                $('li', select).each(function() {
                    $(this).toggle($(this).text().toLowerCase().startsWith(searchText));
                });
            }).on('click mousedown', function(e) {
                e.stopPropagation();
            });

            $('#select-menu', toolbar).on('shown.bs.dropdown', function() {
                $('.select-menu-search input').focus();
            });
        });
    };
})(jQuery);

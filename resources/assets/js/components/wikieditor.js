(function ($) {
    'use strict';

    var languages = {
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
            var textarea    = $(this);
            var toolbar     = $('#wiki-toolbar');
            var select      = $('.select-menu-wrapper', toolbar).find('ul');

            $.each(languages, function(key, value) {
                select.append('<li><a data-open="<br>```' + key + '" data-close="<br>```" title="Kod źródłowy: ' + value + '">' + value + '</a></li>');
            });

            $('a[data-open], button[data-open]', toolbar).click(function() {
                textarea.insertAtCaret($(this).data('open').replace(/<br>/g, "\n"), $(this).data('close').replace(/<br>/g, "\n"), ' ');
            });

            $(textarea).bind('keydown', function(e) {
                if ((e.which === 9 || e.keyCode === 9) && e.shiftKey) {
                    textarea.insertAtCaret("\t", '', "");

                    return false;
                }
            });

            $('.select-menu-search input', toolbar).keyup(function(e) {
                var searchText = $.trim($(this).val().toLowerCase());

                $('li', select).each(function() {
                    $(this).toggle($(this).text().toLowerCase().startsWith(searchText));
                });
            });

            $('#select-menu', toolbar).on('shown.bs.dropdown', function() {
                $('.select-menu-search input').focus();
            });
        });
    };

    $.fn.extend({
        insertAtCaret: function (openWith, closeWith, value) {
            var element = this[0];

            if (document.selection) {
                element.focus();
                var sel = document.selection.createRange();
                sel.text = openWith + (sel.text.length > 0 ? sel.text : value) + closeWith;

                element.focus();
            }
            else if (element.selectionStart || element.selectionStart == '0') {
                var startPos = element.selectionStart;
                var endPos = element.selectionEnd;
                var scrollTop = element.scrollTop;

                if (startPos !== endPos) {
                    value = openWith + element.value.substring(startPos, endPos) + closeWith;
                }
                else {
                    value = openWith + value + closeWith;
                }

                element.value = element.value.substring(0, startPos) + value + element.value.substring(endPos, element.value.length);

                element.focus();
                element.selectionStart = startPos + value.length;
                element.selectionEnd = startPos + value.length;
                element.scrollTop = scrollTop;
            }
            else {
                element.value += (openWith + value + closeWith);
                element.focus();
            }
        }
    });
})(jQuery);

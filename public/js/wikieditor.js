(function ($) {
    'use strict';

    $.fn.wikiEditor = function () {
        return this.each(function () {
            var textarea = $(this);
            var toolbar = $('#wiki-toolbar');

            $('.btn-group button', toolbar).click(function() {
                textarea.insertAtCaret($(this).data('open').replace(/<br>/g, "\n"), $(this).data('close').replace(/<br>/g, "\n"), ' ');
            });

            //$(textarea).bind($.browser.opera ? 'keypress' : 'keydown', function(e)
            $(textarea).bind('keydown', function (e) {
                if ((e.which === 9 || e.keyCode === 9) && e.shiftKey) {
                    textarea.insertAtCaret("\t", '', "");

                    return false;
                }
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
//# sourceMappingURL=wikieditor.js.map
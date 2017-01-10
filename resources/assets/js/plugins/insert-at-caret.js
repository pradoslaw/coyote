$(function() {
    'use strict';

    $.fn.insertAtCaret = function (openWith, closeWith, value) {
        let element = this[0];

        if (document.selection) {
            element.focus();
            let sel = document.selection.createRange();
            sel.text = openWith + (sel.text.length > 0 ? sel.text : value) + closeWith;

            element.focus();
        }
        else if (element.selectionStart || element.selectionStart == '0') {
            let startPos = element.selectionStart;
            let endPos = element.selectionEnd;
            let scrollTop = element.scrollTop;

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
    };
});

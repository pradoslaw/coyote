import Textarea from '../libs/textarea';

$(function() {
    'use strict';

    // wsteczna kompatybilnosc
    // @todo do usuniecia na rzecz klasy Textarea
    $.fn.insertAtCaret = function (startsWith, endsWith, value) {
        let el = new Textarea(this[0]);

        el.insertAtCaret(startsWith, endsWith, el.isSelected() ? el.getSelection() : value);
    };
});

$(function() {
    "use strict";

    $('#btn-toggle-sidebar').click(function () {
        var sidebar = $('#sidebar');

        if (sidebar.css('display') === 'block') {
            // usuwamy atrybut "style" aby przegladarka "wziela" wartosc z pliku css
            sidebar.removeAttr('style');
        }
        else {
            sidebar.css('display', 'block');
        }
    });
});

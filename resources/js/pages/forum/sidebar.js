$(function () {
    "use strict";

    let sidebar = $('#sidebar');

    let handler = function (e) {
        if ($(e.target).closest(sidebar).length === 0) {
            e.preventDefault();
            close();
        }
    };

    let close = function () {
        // usuwamy atrybut "style" aby przegladarka "wziela" wartosc z pliku css
        sidebar.removeAttr('style');
        $(document).unbind("click touchstart", handler);
    };

    let open = function () {
        sidebar.css('display', 'block');
        $(document).bind("click touchstart", handler);
    };

    $('#btn-toggle-sidebar').click(function () {
        sidebar.css('display') === 'block' ? close() : open();
        return false;
    });
});

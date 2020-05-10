$(function () {
    'use strict';

    // $('.navbar-toggle').each(function () {
    //     let menu = $(this).attr('data-target');
    //
    //     let handler = function (e) {
    //         if ($(e.target).closest('.nav').length === 0) {
    //             e.preventDefault();
    //             $(menu).collapse('hide');
    //         }
    //     };
    //
    //     $(menu).on('shown.bs.collapse', function () {
    //         $(document).bind("click touchstart", handler);
    //     });
    //
    //     $(menu).on('hidden.bs.collapse', function () {
    //         $(document).unbind("click touchstart", handler);
    //     });
    // });

    // tymczasowy test: mozliwosc zmiany menu na nowe/stare
    $('.js-change-menu').click(() => {
        let header = $('.navbar');

        header.toggleClass('navbar-dark bg-dark bg-light navbar-light');

        $.post('/User/Settings/Ajax', {'dark_theme': +header.hasClass('navbar-dark')});
    });
});

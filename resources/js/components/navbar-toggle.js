$(function () {
    'use strict';

    // tymczasowy test: mozliwosc zmiany menu na nowe/stare
    $('.js-change-menu').click(() => {
        let header = $('.navbar');

        header.toggleClass('navbar-dark bg-dark bg-light navbar-light');

        $.post('/User/Settings/Ajax', {'dark_theme': +header.hasClass('navbar-dark')});
    });
});

$(function() {
    'use strict';

    const AMOUNT_SCROLLED = 300;

    $(window).scroll(() => {
        if ($(window).scrollTop() > AMOUNT_SCROLLED) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });

    $(".back-to-top").click(() => {
        $("html, body").animate({scrollTop: 0}, "fast");

        return false;
    });
});

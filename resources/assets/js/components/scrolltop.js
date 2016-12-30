$(function() {
    'use strict';

    var AMOUNT_SCROLLED = 300;

    $(window).scroll(function () {
        if ($(window).scrollTop() > AMOUNT_SCROLLED) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });

    $(".back-to-top").click(function () {
        $("html, body").animate({scrollTop: 0}, "fast");

        return false;
    });
});

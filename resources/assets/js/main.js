$(function() {
    'use strict';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // if navbar class navbar-fixed-top class, we must scroll to appropriate page element according to
    // window.location.hash.
    $(window).load(function() {
        if (window.location.hash !== '' && $('header.navbar-default').hasClass('navbar-fixed-top')) {
            var target = $(window.location.hash);

            if (target.length) {
                $('html, body').animate({ scrollTop: target.offset().top - 60 });
            }
        }
    });
});
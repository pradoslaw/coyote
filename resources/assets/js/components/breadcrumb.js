$(function () {
    'use strict';

    let handler = function () {
        if ($(window).scrollTop() > 150) {
            let breadcrumb = $('#breadcrumb-fixed');
            let css = {left: $('#logo').position().left, opacity: 1.0};

            if (!breadcrumb.length) {
                breadcrumb = $('.breadcrumb:first:visible').clone();

                // breadcrumb can be empty
                if ($.trim(breadcrumb.text()).length > 0) {
                    breadcrumb.attr({id: 'breadcrumb-fixed'}).css(css).hide().appendTo('body');

                    breadcrumb.slideDown('slow', (e) => $(e.currentTarget).animate({opacity: 0.0}, 5000));
                    breadcrumb.hover((e) => $(e.currentTarget).stop().css(css), (e) => $(e.currentTarget).animate({opacity: 0.0}, 800));
                }
            }
            else {
                breadcrumb.stop(true, true).css(css).animate({opacity: 0.0}, 5000);
            }
        }
        else {
            $('#breadcrumb-fixed').stop().fadeOut(400, function () {
                $(this).remove();
            });
        }
    };

    // if navbar class navbar-fixed-top class, we must scroll to appropriate page element according to
    // window.location.hash.
    if ('onhashchange' in window) {
        let header = $('header.navbar-default.navbar-fixed-top');

        if (header.length) {
            $(window).bind('hashchange', () => {
                let target = $(window.location.hash);

                if (target.length) {
                    $('html, body').animate({ scrollTop: target.offset().top - header.outerHeight() - 5 }, 0);
                }
            });
        }

        $(window).load(() => {
            if (window.location.hash !== '') {
                $(window).trigger('hashchange');
            }
        });
    }

    let isMobile = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));

    if (!isMobile && $('header.navbar-fixed-top').length === 1) {
        if (window.location.hash.length) {
            setTimeout(() => $(window).scroll(handler), 2000);
        }
        else {
            $(window).scroll(handler);
        }
    }
});

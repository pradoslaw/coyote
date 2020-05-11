$(function () {
    'use strict';

    let handler = function () {
        if ($(window).scrollTop() > 150) {
            let breadcrumb = $('#breadcrumb-fixed');
            let css = {left: $('.navbar-brand').position().left, opacity: 1.0};

            if (!breadcrumb.length) {
                breadcrumb = $('.breadcrumb:first:visible').clone();

                // breadcrumb can be empty
                if ($.trim(breadcrumb.text()).length > 0) {
                    breadcrumb.attr({id: 'breadcrumb-fixed'}).css(css).hide().appendTo('body');

                    breadcrumb.slideDown('fast');
                }
            }
            else {
                breadcrumb.css(css);
            }
        }
        else {
            $('#breadcrumb-fixed').remove();
        }
    };

    // if navbar class has fixed-top class, we must scroll to appropriate page element according to
    // window.location.hash.
    if ('onhashchange' in window) {
        let header = $('.fixed-top');

        if (header.length) {
            $(window).bind('hashchange', () => {
                let target = $(window.location.hash);

                if (target.length) {
                    $('html, body').animate({ scrollTop: target.offset().top - header.outerHeight() - 50 }, 0);
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

    if (!isMobile && $('.fixed-top').length === 1) {
        $(window).scroll(handler);
    }
});

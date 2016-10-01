$(function () {
    'use strict';

    var handler = function () {
        if ($(window).scrollTop() > 150) {
            var breadcrumb = $('#breadcrumb-fixed');

            if (!breadcrumb.length) {
                breadcrumb = $('.breadcrumb:first:visible').clone();

                if ($.trim(breadcrumb.text()).length > 0) {
                    breadcrumb.attr({id: 'breadcrumb-fixed'}).hide().appendTo('body');
                    breadcrumb.css({left: $('#logo').position().left}).slideDown('slow', function () {
                        $(this).animate({opacity: 0.0}, 5000);
                    });

                    breadcrumb.hover(function () {
                        $(this).stop().css('opacity', 1.0);
                    },
                    function () {
                        $(this).animate({opacity: 0.0}, 800);
                    });
                }
            }
            else {
                breadcrumb.stop(true, true).css('opacity', 1.0).animate({opacity: 0.0}, 5000);
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
        var header = $('header.navbar-default.navbar-fixed-top');

        if (header.length) {
            $(window).bind('hashchange', function() {
                var target = $(window.location.hash);

                if (target.length) {
                    $('html, body').animate({ scrollTop: target.offset().top - header.outerHeight() - 5 }, 0);
                }
            });
        }

        $(window).load(function() {
            if (window.location.hash !== '') {
                $(window).trigger('hashchange');
            }
        });
    }

    var isMobile = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));

    if (!isMobile && $('#front-end').length === 1) {
        if (window.location.hash.length) {
            setTimeout(function() {
                $(window).scroll(handler);
            }, 2000);
        }
        else {
            $(window).scroll(handler).trigger('scroll');
        }
    }
});

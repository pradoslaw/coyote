$(function () {
    'use strict';

    var handler = function () {
        if ($(window).scrollTop() > 150) {
            var breadcrumb = $('#breadcrumb-fixed');

            if (!breadcrumb.length) {
                breadcrumb = $('.breadcrumb:first').clone();

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

    var isMobile = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));

    if (!isMobile && $('#front-end').length === 1) {
        if (window.location.hash.length) {
            setTimeout(function() {
                $(window).scroll(handler);
            }, 1000);
        }
        else {
            $(window).scroll(handler).trigger('scroll');
        }
    }
});

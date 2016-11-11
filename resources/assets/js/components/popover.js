$(function() {
    'use strict';

    var popover = Session.getItem('popover');

    if (popover === null) {
        popover = [];
    } else {
        popover = JSON.parse(popover);
    }

    $('.alert-popover').each(function() {
        if ($.inArray($(this).data('id'), popover) === -1) {
            if ($(this).data('containment')) {
                var p = $($(this).data('containment')).offset();
                var css = {'left': p.left};

                if ($(this).hasClass('bottom')) {
                   css.top = p.top - $(this).outerHeight() - 15;
                }
                else if ($(this).hasClass('top')) {
                    css.top = p.top + $(this).outerHeight() + 15;
                }
                else if ($(this).hasClass('right')) {
                    css.left = p.left - $(this).outerWidth() - 15;
                    css.top = p.top;
                }

                $(this).css(css);
            }

            $(this).fadeIn(400);
        }
    }).on('click', '.close', function() {
        popover.push($(this).parent().data('id'));
        Session.setItem('popover', JSON.stringify(popover));

        $(this).parent().hide();
    });
});

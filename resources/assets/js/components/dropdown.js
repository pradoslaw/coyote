(function ($) {
    'use strict';

    function clearMenus() {
        $('.dropdown-backdrop').remove();

        $('[data-toggle="dropdown"]').each(function() {
            var $this = $(this);
            var $parent = $this.parent();

            if (!$parent.hasClass('open')) {
                return;
            }

            $this.next('.dropdown-menu').hide();
            $parent.removeClass('open');
        });
    }

    $('body')
        .on('click', '[data-toggle="dropdown"]', function(e) {
            var $this = $(this);
            var $parent = $this.parent();
            var dropdown = $this.next('.dropdown-menu');
            var isOpen = $parent.hasClass('open');

            clearMenus();

            if (!isOpen) {
                dropdown.show();
                $parent.addClass('open');

                if ('ontouchstart' in document.documentElement  && !$parent.closest('.navbar-nav').length) {
                    // if mobile we use a backdrop because click events don't delegate
                    $(document.createElement('div')).addClass('dropdown-backdrop').insertAfter($this).on('click', clearMenus);
                }

                if (e.isDefaultPrevented()) {
                    return;
                }

                $this
                    .trigger('focus')
                    .attr('aria-expanded', 'true');
            }
        })
        .on('keydown', '[data-toggle="dropdown"]', function(e) {
            if (e.which === 27 && $(this).parent().hasClass('open')) {
                $(this).trigger('click');
            }
        });


    $.fn.dropdown = function() {
        $(this).trigger('click');
    };

    $(document).on('click', function(e) {
        var target = $(e.target);
        var selectors = '.dropdown-menu, [data-toggle="dropdown"]';

        if (e.which !== 2 && !target.is(selectors) && !target.parent().is(selectors)) {
            clearMenus();
        }
    });
})(jQuery);

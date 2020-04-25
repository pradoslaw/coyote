(function ($) {
    "use strict";

    $.fn.fastSubmit = function () {
        return this.each(function () {
            $(this).keydown(function(e) {
                if (e.keyCode === 13) {
                    let form = $(this).closest('form');
                    let isDisabled = form.find(':submit').attr('disabled') === 'disabled';

                    if ((e.ctrlKey || e.metaKey) && !isDisabled) {
                        form.submit();
                    }
                }
            });
        });
    };
})(jQuery);

(function ($) {
    "use strict";

    $.fn.fastSubmit = function () {
        return this.each(function () {
            $(this).keydown(function(e) {
                if (e.keyCode === 13) {
                    var form = $(this).closest('form');

                    if (e.ctrlKey) {
                        form.submit();
                    }
                }
            });
        });
    };
})(jQuery);
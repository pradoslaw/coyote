(function ($) {
    "use strict";

    $.fn.escape = function (cb) {
        return this.each(function() {
            $(this).keydown(e => {
                if (e.keyCode === 27) {
                    cb();
                }
            });
        });
    };
})(jQuery);

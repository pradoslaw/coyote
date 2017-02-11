(function ($) {
    'use strict';

    $.fn.pasteImage = function (complete) {
        return this.each(function () {
            var setup = {
                complete: function (textarea, result) {
                    textarea.insertAtCaret('![' + result.name + '](' + result.url + ')', '', ' ');
                }
            };

            var textarea = $(this);
            setup.url = textarea.data('paste-url');

            if (typeof setup.url === 'undefined') {
                alert('Textarea does not have data-paste-url attribute');
            }

            if (typeof complete !== 'undefined') {
                setup.complete = complete;
            }

            var upload = function(base64) {
                textarea.attr('readonly', 'readonly');

                var p = textarea.offset();
                var loader = $('<div id="ajax-loader"><i class="fa fa-cog fa-spin"></i></div>').css({top: p.top, left: p.left, width: textarea.outerWidth(), height: textarea.outerHeight()}).appendTo('body');

                $.post(setup.url, base64, function(result) {
                    setup.complete(textarea, result);
                })
                .always(function() {
                    textarea.removeAttr('readonly');
                    loader.remove();
                });
            };

            if ('onpaste' in textarea[0]) {
                textarea[0].onpaste = function(e) {
                    var items = [];

                    if (e.clipboardData && e.clipboardData.items) {
                        items = e.clipboardData.items;
                    }

                    if (items.length) {
                        var blob = items[0].getAsFile();
                        var fr = new FileReader();

                        fr.onload = function(e) {
                            var mime = /^data:image/g;

                            if (!mime.test(e.target.result)) {
                                return false;
                            }

                            upload(e.target.result);
                        };

                        if (blob) {
                            fr.readAsDataURL(blob);
                        }
                    }
                };
            }
        });
    };
})(jQuery);

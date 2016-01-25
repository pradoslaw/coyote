(function ($) {
    'use strict';

    $.fn.pasteImage = function (options) {
        return this.each(function () {
            var defaults = {
                url: '/User/Pm/Paste',
                onBeforeSend: function () {
                    //
                },
                onComplete: function (textarea, result) {
                    textarea.insertAtCaret('![' + result.name + '](' + result.url + ')', '', ' ');
                }
            };

            var setup = $.extend(defaults, options);console.log(setup,options);
            var textarea = $(this);

            var upload = function(base64) {
                textarea.attr('readonly', 'readonly');
                setup.onBeforeSend();

                var p = textarea.offset();
                var loader = $('<div id="ajax-loader"><i class="fa fa-cog fa-spin"></i></div>').css({top: p.top, left: p.left, width: textarea.outerWidth(), height: textarea.outerHeight()}).appendTo('body');

                $.post(setup.url, base64, function(result) {
                    setup.onComplete(textarea, result);
                })
                .always(function() {
                    textarea.removeAttr('readonly');
                    loader.remove();
                });
            };

            if (navigator.userAgent.indexOf("Chrome") > -1) {
                textarea[0].onpaste = function(e) {
                    var items = e.clipboardData.items;

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

                        fr.readAsDataURL(blob);
                    }
                };
            } else if (navigator.userAgent.indexOf("Firefox") > -1 && navigator.userAgent.indexOf("Trident") === -1) {
                var $placeholder = $('<div id="placeholder" contenteditable="true" style="position: absolute; left: -10000px; top: -10000px; width: 1px; height: 1px;"></div>');

                textarea.keydown(function(e) {
                    if (e.ctrlKey && !e.shiftKey && !e.altKey && e.which === 86) {
                        var top = $(window).scrollTop();
                        $placeholder.css('top', top);

                        $placeholder.focus();
                    }
                });

                $placeholder.on('paste', function(e) {
                    var $placeholder = $(this);
                    var data = (e.originalEvent || e).clipboardData.getData('text/plain');

                    if (data) {
                        if (textarea[0].selectionStart !== textarea[0].selectionEnd) {
                            textarea[0].value  = textarea[0].value.substring(0, textarea[0].selectionStart) + data + textarea[0].value.substring(textarea[0].selectionEnd, textarea[0].value.length);
                        } else {
                            textarea.insertAtCaret(data, '', '');
                        }

                        $placeholder.html('');
                    } else {
                        setTimeout(function() {
                            var image = $('img', $placeholder).get(0);
                            if (image !== undefined) {
                                data = image.src;
                                upload(data);
                            }

                            $placeholder.html('');

                        }, 60);
                    }
                })
                .appendTo('body');
            }
        });
    };
})(jQuery);
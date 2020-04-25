(function ($) {
    $.fn.prompt = function () {
        return this.each(function () {
            var $textarea = $(this);
            var index = -1;
            var timeId = 0;
            var $ul = $('<ul style="display: none;" class="auto-complete"></ul>');

            var url = $textarea.data('prompt-url');

            if (typeof url === 'undefined') {
                alert('Textarea does not have data-prompt-url attribute');
            }

            var getCursorPosition = function () {
                if ($textarea[0].selectionStart || $textarea[0].selectionStart == 0) {
                    return $textarea[0].selectionStart;
                }
                else if (document.selection) {
                    $textarea.focus();
                    var sel = document.selection.createRange();

                    sel.moveStart('character', -$textarea.value.length);
                    return (sel.text.length);
                }
            };

            var getUserNamePosition = function (caretPosition) {
                var i = caretPosition;
                var result = -1;

                while (i > caretPosition - 50 && i >= 0) {
                    var $val = $textarea.val()[i];

                    if ($val === ' ') {
                        break;
                    }
                    else if ($val === '@') {
                        if (i === 0 || $textarea.val()[i - 1] === ' ' || $textarea.val()[i - 1] === "\n") {
                            result = i + 1;
                            break;
                        }
                    }
                    i--;
                }

                return result;
            };

            var onSelect = function (position) {
                var length = $('li', $ul).length;

                if (length > 0) {
                    if (position >= length) {
                        position = 0;
                    }
                    else if (position < 0) {
                        position = length - 1;
                    }
                    index = position;

                    $('li', $ul).removeClass('hover');
                    $('li:eq(' + index + ')', $ul).addClass('hover');
                }
            };

            $textarea.bind('keyup click', function (e) {
                var userName = '';
                var keyCode = e.keyCode/* || window.event.keyCode*/;
                var caretPosition = getCursorPosition();

                var startIndex = getUserNamePosition(caretPosition);

                if (startIndex > -1) {
                    userName = $textarea.val().substr(startIndex, caretPosition - startIndex);
                }

                var onClick = function () {
                    var $text = $('li.hover span', $ul).text();

                    if ($text.length) {
                        if ($text.indexOf(' ') > -1 || $text.indexOf('.') > -1) {
                            $text = '{' + $text + '}';
                        }

                        if (startIndex === 1) {
                            $text += ': ';
                        }

                        $textarea.val($textarea.val().substr(0, startIndex) + $text + $textarea.val().substring(caretPosition)).trigger('change').focus();
                        var caret = startIndex + $text.length;

                        if ($textarea[0].setSelectionRange) {
                            $textarea[0].setSelectionRange(caret, caret);
                        }
                        else if ($textarea[0].createTextRange) {
                            var range = $textarea[0].createTextRange();

                            range.collapse(true);
                            range.moveEnd('character', caret);
                            range.moveStart('character', caret);
                            range.select();
                        }
                    }

                    $ul.html('').hide();
                };

                switch (keyCode) {
                    // esc
                    case 27:

                        $ul.html('').hide();
                        break;

                    // down
                    case 40:

                        onSelect(index + 1);
                        break;

                    case 38:

                        onSelect(index - 1);
                        break;

                    case 13:

                        onClick();

                        break;

                    default:

                        if (userName.length >= 2) {
                            clearTimeout(timeId);

                            timeId = setTimeout(function () {
                                $.get(url, {q: userName}, function (html) {
                                    $ul.html(html).hide();
                                    var length = $('li', $ul).length;

                                    if (length > 0) {
                                        var p = $textarea.offset();

                                        $('li', $ul)
                                            .click(onClick)
                                            .hover(
                                            function () {
                                                $('li', $ul).removeClass('hover');
                                                $(this).addClass('hover');
                                            },
                                            function () {
                                                $(this).removeClass('hover');
                                            }
                                        );

                                        $ul.css({
                                            'width': $textarea.outerWidth(),
                                            'top': $textarea.outerHeight() + p.top + 1,
                                            'left': p.left
                                        }).show();

                                        index = -1;
                                    }
                                })
                                    .error(function(e) {
                                        e.preventDefault();

                                        // do nothing
                                        return false;
                                    });

                            }, 200);
                        }
                        else {
                            $ul.html('').hide();
                        }

                        break;
                }
            }).keydown(function (e) {
                var keyCode = e.keyCode;

                if ((keyCode === 40 || keyCode === 38 || keyCode === 13 || keyCode === 27) && $ul.is(':visible')) {
                    e.preventDefault();
                    return false;
                }
            });

            $ul.appendTo(document.body);

            $(document).bind('click', function (e) {
                var $target = $(e.target);

                if ($target.not($ul)) {
                    $ul.html('').hide();
                }
            });
        });
    };
})(jQuery);

(function ($) {
    'use strict';

    $.fn.tag = function (config) {
        return this.each(function () {
            var $this = $(this);
            var name = $this.attr('name') + '[]';

            var dropdown = $('<ol class="tag-dropdown"></ol>').hide();
            var index = -1;
            var timeId;

            $this.removeAttr('name');

            $this.wrap('<div class="form-control tag-editor"></div>');
            var editor = $('.tag-editor');
            var editorWidth = editor.width();

            dropdown.css({
                'width': editor.outerWidth(),
                'left': editor.position().left,
                'top': editor.position().top + editor.outerHeight()
            });

            $this.attr('autocomplete', 'off').removeClass('form-control');
            dropdown.insertAfter(editor);

            function setInputWidth() {
                $(':hidden[name="' + name + '"]').remove();

                $('ul.tag-clouds li', editor).each(function () {
                    $('<input>', {'type': 'hidden', 'name': name}).val($(this).text()).insertAfter($this);
                });

                var width = editorWidth - $('.tag-clouds', editor).outerWidth();
                $this.width(Math.max(100, width));

                if (width < 100) {
                    width = width < 0 ? Math.abs(width - 100) : 100 - width;

                    $('.tag-clouds', editor).css('left', -width);
                    $this.css('left', -width);

                    editor.width(editorWidth);
                }
                else {
                    $this.css('left', 0);
                    $('.tag-clouds', editor).css('left', 0);
                }
            }

            function filterData(value) {
                // @todo odczytywac z pola z klasy TagValidator
                value = $.trim(value.toLowerCase().replace(/[^a-ząęśżźćółń0-9\-\.#\+\s]/gi, ''));

                if (value.startsWith('#')) {
                    value = value.substr(1);
                }

                return value;
            }

            editor.prepend('<ul class="tag-clouds"></ul>');

            if ($.trim($this.val()) !== '') {
                $.each($this.val().split(','), function (index, chunk) {
                    editor.children('ul').append('<li><a class="remove">' + $.trim(chunk) + '</a></li>');
                });
            }

            setInputWidth();
            $this.val('');

            $('.tag-clouds', editor).delegate('a.remove', 'click', function () {
                $(this).parent().remove();
                setInputWidth();
            });

            var onSelect = function (position) {
                var length = $('li:visible', dropdown).length;

                if (length > 0) {
                    if (position >= length) {
                        position = 0;
                    }
                    else if (position < 0) {
                        position = length - 1;
                    }
                    index = position;

                    $('li:visible', dropdown).removeClass('hover');
                    $('li:visible:eq(' + index + ')', dropdown).addClass('hover');
                }
            };

            var onChange = function (value, setFocus) {
                $('li', dropdown).removeClass('hover').show();
                index = -1;

                value = filterData(value);

                if (value !== '') {
                    editor.children('ul').append('<li><a class="remove">' + value + '</a></li>');
                    setInputWidth();

                    if (config.validateUrl) {
                        $.get(config.validateUrl, {t: value}).fail(function (e) {
                            if (typeof e.responseJSON.t !== 'undefined') {
                                $('#alert').modal('show').find('.modal-body').text(e.responseJSON.t[0]);
                                $('ul.tag-clouds li:last', editor).remove();
                                setInputWidth();
                            }
                        });
                    }
                }

                clearTimeout(timeId);
                dropdown.hide();
                $this.val('');

                if (setFocus !== false) {
                    $this.focus();
                }
            };

            dropdown.delegate('li', 'click', function () {
                onChange($(this).children('span').text());
            })
            .delegate('li', 'mouseenter mouseleave', function (e) {
                if (e.type === 'mouseenter') {
                    $(this).addClass('hover');
                }
                else {
                    $(this).removeClass('hover');
                }
            });

            $this.keydown(function (e) {
                var keyCode = e.keyCode || window.event.keyCode;

                if (keyCode === 27) {
                    $this.val('');
                    dropdown.hide();
                }
                else if (keyCode === 13 && $this.val() !== '') {
                    e.preventDefault();
                }
            })
            .keyup(function (e) {
                var keyCode = e.keyCode || window.event.keyCode;

                if (keyCode === 40) {// down
                    onSelect(index + 1);
                }
                else if (keyCode === 38) { // up
                    onSelect(index - 1);
                }
                else if (keyCode === 13) {
                    if ($('li.hover', dropdown).text() !== '') {
                        onChange($('li.hover span', dropdown).text());
                        e.preventDefault();
                    }
                    else if ($this.val() !== '') {
                        onChange($this.val());
                        e.preventDefault();
                    }
                }
                else if (keyCode === 8 && $this.val().length === 0) {
                    var tag = $('ul.tag-clouds li:last', editor).text();

                    if (tag) {
                        $('ul.tag-clouds li:last', editor).remove();
                        setInputWidth();

                        $this.val(' ' + tag);
                    }
                }
                else if (keyCode === 32 || keyCode === 188) {
                    onChange($this.val());
                }
                else {
                    var searchText = filterData($(this).val());

                    clearTimeout(timeId);

                    if (searchText.length) {
                        timeId = setTimeout(function () {
                            $.get(config.promptUrl, {q: searchText}, function (html) {
                                if ($.trim(html) !== '') {
                                    dropdown.html(html).css('top', editor.position().top + editor.outerHeight()).show();
                                }
                                else {
                                    dropdown.hide();
                                }
                            });

                        }, 200);
                    }
                    else {
                        dropdown.hide();
                    }
                }
            })
            .blur(function () {
                if (dropdown.is(':hidden')) {
                    if ($this.val() !== '') {
                        onChange($this.val(), false);
                    }
                }
            });

            $(document).bind('click', function (e) {
                var $target = $(e.target);

                if (!$target.is($this)) {
                    if ($this.val() !== '') {
                        onChange($this.val(), false);
                    }

                    dropdown.hide();
                }
            });
        });
    };
})(jQuery);

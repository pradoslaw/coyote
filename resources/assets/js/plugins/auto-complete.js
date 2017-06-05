/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

(function ($) {
    'use strict';

    $.fn.autocomplete = function (options) {
        var defaults =
        {
            className: 'auto-complete',
            autoSubmit: true,
            minLength: 2,
            url: '',
            delay: 200,
            onChange: function (value) {

            }
        };

        var setup = $.extend(defaults, options);

        var $this = $(this);
        var dropdown = $('<ul style="display: none;" class="' + setup.className + '"></ul>');
        var index = -1;
        var timeId = 0;

        dropdown.css({
            'width': $this.outerWidth(),
            'left': $this.position().left,
            'top': $this.position().top + $this.outerHeight()
        });

        $this.attr('autocomplete', 'off');
        dropdown.insertAfter($this);

        var select = function (position) {
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

                dropdown.scrollTop(position * $('li:first', dropdown).outerHeight());
            }
        };

        var change = function (value) {
            value = $.trim(value).toString();

            $('li', dropdown).removeClass('hover').show();
            $this.val(value);
            setup.onChange(value);

            index = -1;

            if (setup.autoSubmit) {
                $this.parents('forms').submit();
            }
        };

        dropdown.on({
            mouseenter: function() {
                $(this).addClass('hover');
            },
            mouseleave: function() {
                $(this).removeClass('hover');
            }
        }, 'li')
        .on('click', 'li', function () {
            change($('span', this).text());
            dropdown.hide();
        });

        $this.keydown(function (e) {
            var keyCode = e.keyCode || window.event.keyCode;

            if (keyCode === 27 || keyCode === 9) {
                dropdown.hide();
            }
            else if (keyCode === 13) {
                e.preventDefault();
            }
        })
        .keyup(function (e) {
            var keyCode = e.keyCode || window.event.keyCode;

            if (keyCode === 40) // down
            {
                select(index + 1);
            }
            else if (keyCode === 38) // up
            {
                select(index - 1);
            }
            else if (keyCode === 13) {
                if (dropdown.is(':visible') && $('li.hover', dropdown).text() !== '') {
                    change($('li.hover span', dropdown).text());
                    dropdown.hide();
                    e.preventDefault();
                }
            }
            else {
                var searchText = $(this).val();

                clearTimeout(timeId);
                timeId = setTimeout(function () {
                    if ($.trim(searchText).length >= setup.minLength) {
                        $.get(setup.url, {q: searchText}, function (html) {
                            dropdown.html(html).hide();
                            dropdown.toggle($('li', dropdown).length > 0);

                            if ($('li:first', dropdown).text().toLowerCase() === $this.val().toLowerCase()) {
                                dropdown.hide();
                            }
                        });
                    }
                    else {
                        dropdown.hide();
                    }

                }, setup.delay);
            }
        });

        $(document).bind('click', function (e) {
            var $target = $(e.target);

            if (!$target.is($this)) {
                dropdown.hide();
            }
        });
    };
})(jQuery);

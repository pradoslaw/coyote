(function ($) {
    "use strict";

    $.fn.autogrow = function () {
        /**
         * @see http://code.google.com/p/gaequery/source/browse/trunk/src/static/scripts/jquery.autogrow-textarea.js?r=2
         */
        return this.each(function () {
            var $this = $(this),
                minHeight = $this.height(),
                maxHeight = 300,
                lineHeight = $this.css('lineHeight'),
                currentWidth = 0,
                currentHeight = 0;

            var shadow = $('<div></div>').css(
            {
                position: 'absolute',
                top: -10000,
                left: -10000,
                width: $(this).width() - parseInt($this.css('paddingLeft')) - parseInt($this.css('paddingRight')),
                fontSize: $this.css('fontSize'),
                fontFamily: $this.css('fontFamily'),
                lineHeight: $this.css('lineHeight'),
                resize: 'none'
            }).appendTo(document.body);

            var update = function () {
                var times = function (string, number) {
                    for (var i = 0, r = ''; i < number; i++) r += string;
                    return r;
                };

                var val = this.value.replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/&/g, '&amp;')
                    .replace(/\n$/, '<br/>&nbsp;')
                    .replace(/\n/g, '<br/>')
                    .replace(/ {2,}/g, function (space) {
                        return times('&nbsp;', space.length - 1) + ' '
                    });

                shadow.html(val);

                $(this).css('height', Math.max(Math.min(shadow.height() + 17, maxHeight), minHeight));
            };

            $this.change(update).keyup(update).keydown(update);
            update.apply(this);

            $this.mousedown(function () {
                currentWidth = $this.width();
                currentHeight = $this.height();
            })
            .mouseup(function () {
                if ($this.width() != currentWidth || $this.height() != currentHeight) {
                    $this.unbind('keyup', update).unbind('keydown', update).unbind('change', update);
                }

                currentWidth = currentHeight = 0;
            });
        });
    };
})(jQuery);
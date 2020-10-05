(function ($) {
    "use strict";

    $.fn.autogrow = function () {
        /**
         * @see http://code.google.com/p/gaequery/source/browse/trunk/src/static/scripts/jquery.autogrow-textarea.js?r=2
         */
        return this.each(function () {
            var $this = $(this),
                minHeight = $this.outerHeight(),
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
                paddingTop: $this.css('paddingTop'),
                paddingBottom: $this.css('paddingBottom'),
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

                $(this).css('height', Math.max(Math.min(shadow.outerHeight() + 2, maxHeight), minHeight));

                currentWidth = $this.outerWidth();
                currentHeight = $this.outerHeight();
            };

            $this.change(update).keyup(update).keydown(update);
            update.apply(this);

            // unbind events if textarea is being resized
            $this.mousedown(function () {
                currentWidth = $this.outerWidth();
                currentHeight = $this.outerHeight();
            })
            .mouseup(function () {
                if ($this.outerWidth() !== currentWidth || $this.outerHeight() !== currentHeight) {
                    $this.unbind('keyup', update).unbind('keydown', update).unbind('change', update);
                }
            });
        });
    };
})(jQuery);

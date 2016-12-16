var DesktopNotifications =
{
    isSupported: function () {
        return ('Notification' in window && window['Notification'] !== null);
    },

    requestPermission: function () {
        if (this.isSupported() && Notification.permission !== 'granted') {
            Notification.requestPermission(function (status) {
                if (Notification.permission !== status) {
                    Notification.permission = status;
                }
            });
        }
    },

    isAllowed: function () {
        return this.isSupported() && Notification.permission === "granted";
    },

    doNotify: function (title, body, url) {
        if (this.isAllowed()) {
            var notification = new Notification(title, {body: body, tag: url, icon: _config.cdn + '/img/favicon.png'});

            notification.onshow = function () {
                setTimeout(function () {
                    notification.close();
                }, 5000);
            };

            return true;
        }
        return false;
    }
};

$(function () {
    'use strict';

    // original page title
    var pageTitle = $('head title').text();

    /**
     * Manager alerts counter (set, get or clear)
     *
     * @type {{self: (jQuery|HTMLElement), set: Alerts.set, get: Alerts.get, clear: Alerts.clear}}
     */
    var Alerts =
    {
        self: $('#alerts'),

        /**
         * Set alerts counter
         *
         * @param value
         */
        set: function(value) {
            if (value > 0) {
                if (!$('.badge', this.self).length) {
                    $('> a:first', this.self).prepend('<span class="badge">' + value + '</span>');
                } else {
                    $('.badge', this.self).text(value);
                }

                $('head title').text('(' + (value) + ') ' + pageTitle);
                $('head link[rel=icon]').attr('href', _config.cdn + '/img/xicon/favicon' + Math.min(value, 6) + '.png');
            } else {
                $('.badge', this.self).remove();
                $('head title').text(pageTitle);
                $('head link[rel=icon]').attr('href', _config.cdn + '/img/favicon.png');
            }
        },

        /**
         * Set alerts counter and save it in local storage.
         *
         * @param value
         */
        store: function(value) {
            Session.setItem('alerts', parseInt(value));

            this.set(value);
        },

        /**
         * Get alerts counter
         *
         * @returns {Number|number}
         */
        get: function() {
            return parseInt(this.self.find('.badge').text()) || 0;
        },

        /**
         * Remove alerts list
         */
        clear: function() {
            $('#dropdown-alerts li').remove();

            if (this.self.hasClass('open')) {
                $('#dropdown-alerts').dropdown('toggle');
            }
        }
    };

    Session.addListener(function (e) {
        if (e.key === 'alerts' && e.newValue !== e.oldValue) {
            Alerts.set(e.newValue);
            Alerts.clear();
        }
    });

    Alerts.self.click(function (e) {
        DesktopNotifications.requestPermission();

        var wrapper = $('#dropdown-alerts');
        var modal = wrapper.find('.dropdown-modal');
        var alerts = modal.find('ul');
        var url = $(this).children().data('url');

        if ($('li', alerts).length <= 1) {
            $('<li><i class="fa fa-spin fa-spinner"></i></li>').appendTo(alerts);

            $.get(url, function (json) {
                alerts.html(json.html);

                Alerts.store(json.unread);

                // default max height of alerts area
                var maxHeight = 390;
                var margin = wrapper.find('.dropdown-header').outerHeight() + 7;

                if (parseInt(Session.getItem('box-notify-h'))) {
                    maxHeight = Math.min(alerts.height(), Math.max(190, parseInt(Session.getItem('box-notify-h'))));
                }

                modal.css('max-height', maxHeight); // max wysokosc obszaru powiadomien

                if (parseInt(Session.getItem('box-notify-w'))) {
                    wrapper.width(parseInt(Session.getItem('box-notify-w')));
                }

                $.getScript(_config.cdn + '/js/jquery-ui.js', function() {
                    wrapper.resizable({
                        maxHeight: alerts.height(), // max rozmiar obszaru powiadomien odpowiada ilosci znajdujacych sie tam powiadomien
                        minHeight: 190,
                        minWidth: 362,
                        resize: function(e, ui) {
                            modal.css('max-height', (ui.size.height - margin));
                        },
                        stop: function(e, ui) {
                            Session.setItem('box-notify-w', ui.size.width);
                            Session.setItem('box-notify-h', ui.size.height - margin);

                            maxHeight = ui.size.height;
                        }
                    });
                });

                if (typeof modal.perfectScrollbar !== 'undefined') {
                    modal.perfectScrollbar('update');
                }

                $.getScript(_config.cdn + '/js/perfect-scrollbar.js', function() {
                    modal.perfectScrollbar({suppressScrollX: true}).on('ps-y-reach-end', function() {
                        $.get(url + '?offset=' + $('li', alerts).length, function(json) {
                            alerts.append(json.html);

                            if ($('li', json.html).length < 10) {
                                modal.off('ps-y-reach-end');
                            }
                        });
                    });
                });
            });
        }

        // return false;
    })
    .delegate('.dropdown-modal li a', 'mousedown', function (e) {
        if ($(this).parent().hasClass('unread')) {
            // klikniecie lewym lub srodkowym przyciskiem myszy
            if (e.which !== 3) {
                $(this).parent().removeClass('unread');
                $.post($(this).data('mark-url'));

                if (e.which === 1) {
                    var pos = $(this).attr('href').indexOf('#');

                    if (pos !== -1) {
                        var hash = $(this).attr('href').substr(pos);
                        if (!$(hash).length) {
                            var url = $(this).attr('href').substr(0, pos);

                            if (url === window.location.href.split('#')[0]) {
                                $(this).attr('href', url + (url.indexOf('?') === -1 ? '?' : '&') + '_=' + ((new Date()).getTime()) + hash);
                            }
                        }
                    }
                }
            }
        }

        return false;
    })
    .delegate('#btn-mark-read', 'click', function() {
        $('li', Alerts.self).removeClass('unread');

        if ($('.badge', Alerts.self).length) {
            Alerts.store(0);
        }

        $.post($(this).attr('href'));
        return false;
    })
    .delegate('.btn-delete-alert', 'click', function() {
        $.post($(this).attr('href'));
        $(this).parent().fadeOut();

        return false;
    });

    $('#messages').click(function() {
        var messages = $('#dropdown-messages').find('ul');

        if ($('li', messages).length <= 1) {
            $.get($(this).children('a').data('url'), function (html) {
                messages.html(html);
            });
        }

        // return false;
    });

    ws.on('alert', function(data) {
        Alerts.store(Alerts.get() + 1);
        Alerts.clear();

        DesktopNotifications.doNotify(data.headline, data.subject, data.url);
    })
    .on('pm', function(data) {
        DesktopNotifications.doNotify(data.senderName, data.excerpt, '#top');

        var dropdown = $('#messages');
        var value = (parseInt($('.badge', dropdown).text()) || 0) + 1;

        if (value === 1) {
            $('> a:first', dropdown).prepend('<span class="badge">' + value + '</span>');
        } else {
            $('.badge', dropdown).text(value);
        }
    });

    setInterval(function() {
        $.get('/ping');
    }, 350000);
});

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

    doNotify: function (title, body) {
        if (this.isAllowed()) {
            var notification = new Notification(title, {body: body, icon: baseUrl + '/img/favicon.png'});

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

    var pageTitle = $('head title').text();

    /**
     * Set alerts number in page title
     *
     * @param value
     */
    var setAlertsNumber = function (value) {
        var alerts = $('#alerts');

        if (value > 0) {
            if (!$('.badge', alerts).length) {
                $('> a:first', alerts).prepend('<span class="badge">' + value + '</span>');
            } else {
                $('.amount', alerts).text(value);
            }

            $('head title').text('(' + (value) + ') ' + pageTitle);
            $('head link[rel=icon]').attr('href', baseUrl + '/img/xicon/favicon' + Math.min(value, 6) + '.png');
        } else {
            $('.badge', alerts).remove();
            $('head title').text(pageTitle);
            $('head link[rel=icon]').attr('href', baseUrl + '/img/favicon.png');
        }
    };

    /**
     * Ilosc NIEprzeczytanych powiadomien
     */
    var alertsUnread = 0;
    var pmUnread = 0;

    Session.addListener(function (e) {
        if (e.key === 'notify' && e.newValue !== e.oldValue) {
            alertsUnread = e.newValue;
            setAlertsNumber(e.newValue);
        }
    });

    $('#alerts').click(function (e) {
        var wrapper = $('#dropdown-alerts');
        var modal = wrapper.find('.dropdown-modal');
        var alerts = modal.find('ul');

        if ($('li', alerts).length <= 1) {
            $.get(baseUrl + '/User/Alerts/Ajax', function (json) {
                alerts.html(json.html);

                Session.setItem('alerts', json.unread);
                setAlertsNumber(json.unread);

                // default max height of alerts area
                var maxHeight = 390;
                var margin = wrapper.find('.dropdown-header').outerHeight() + 7;

                if (parseInt(Session.getItem('box-notify-h'))) {
                    maxHeight = Math.min(alerts.height(), Math.max(190, parseInt(Session.getItem('box-notify-h'))));
                    modal.css('max-height', maxHeight); // max wysokosc obszaru powiadomien
                }

                if (parseInt(Session.getItem('box-notify-w'))) {
                    wrapper.width(parseInt(Session.getItem('box-notify-w')));
                }

                $.getScript(baseUrl + '/js/ui-resizer.js', function() {
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

                $.getScript(baseUrl + '/js/perfect-scrollbar.js', function() {
                    modal.perfectScrollbar({suppressScrollX: true});
                });
            });
        }
    })
    .delegate('.dropdown-modal li a', 'mousedown', function (e) {
        if ($(this).parent().hasClass('unread')) {
            // klikniecie lewym lub srodkowym przyciskiem myszy
            if (e.which !== 3) {
                $(this).parent().removeClass('unread');
                $.post(baseUrl + '/User/Alerts/Mark/' + $(this).data('id'));

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
    .delegate('#btn-mark-read', 'click', function()
    {
        var alerts = $('#alerts');

        $('li', alerts).removeClass('unread');
        Session.setItem('alerts', 0);

        if ($('.badge', alerts).length) {
            setAlertsNumber(0);
        }

        $.post(baseUrl + '/User/Alerts/Mark');
        return false;
    })
    .delegate('.btn-delete-alert', 'click', function() {
        $.post(baseUrl + '/User/Alerts/Delete/' + parseInt($(this).prev().data('id')));
        $(this).parent().fadeOut();

        return false;
    });

    $('#messages').click(function() {
        var messages = $('#dropdown-messages').find('ul');

        if ($('li', messages).length <= 1) {
            $.get(baseUrl + '/User/Pm/Ajax', function (html) {
                messages.html(html);
            });
        }
    });
});
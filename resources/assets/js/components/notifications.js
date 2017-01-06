import DesktopNotifications from '../libs/notifications';
import Session from '../libs/session';

/**
 * Manager alerts counter (set, get or clear)
 */
class Notifications
{
    constructor() {
        this._self = $('#btn-alerts');
        this._dropdown = $('#dropdown-alerts');
        this._modal = this._dropdown.find('.dropdown-modal');

        // original page title
        this._pageTitle = $('head title').text();

        this._self.find('a[data-toggle="dropdown"]').click(this._onDropdownClick.bind(this));
        this._dropdown.on('mousedown', 'li a', this._onItemClick.bind(this)).on('click', '#btn-mark-read', this._onMarkClick.bind(this)).on('click', '.btn-delete-alert', this._onDeleteClick.bind(this));
    }

    /**
     * Set alerts counter
     *
     * @param value
     */
    set(value) {
        if (value > 0) {
            this._setTitle('(' + (value) + ') ' + this._pageTitle);
            this._setIcon(_config.cdn + '/img/xicon/favicon' + Math.min(value, 6) + '.png');
        } else {
            this._setTitle(this._pageTitle);
            this._setIcon(_config.cdn + '/img/favicon.png');
        }

        this._setBadge(value);
    }

    /**
     * Set alerts counter and save it in local storage.
     *
     * @param value
     */
    store(value) {
        Session.setItem('alerts', parseInt(value));

        this.set(value);
    }

    /**
     * Get notifications counter
     *
     * @returns {Number|number}
     */
    get() {
        return parseInt(this._self.find('.badge').text()) || 0;
    }

    /**
     * Remove alerts list
     */
    clear() {
        $('li', this._dropdown).remove();

        if (this._self.hasClass('open')) {
            this._self.dropdown('toggle');
        }
    }

    /**
     * Set unread notification's counter.
     *
     * @param value
     * @private
     */
    _setBadge(value) {
        let badge = $('.badge', this._self);

        if (value === 0) {
            badge.remove();
        }
        else {
            if (!badge) {
                $('> a:first', this._self).prepend('<span class="badge">' + value + '</span>');
            } else {
                badge.text(value);
            }
        }
    }

    /**
     * Set <title> tag.
     *
     * @param title
     * @private
     */
    _setTitle(title) {
        $('head title').text(title);
    }

    /**
     * Set favicon with number of unread notifications.
     *
     * @param url
     * @private
     */
    _setIcon(url) {
        $('head link[rel=icon]').attr('href', url);
    }

    /**
     * Bind onclick on notification element.
     *
     * @param e
     * @private
     */
    _onDropdownClick(e) {
        DesktopNotifications.requestPermission();

        let items = this._modal.find('ul');
        const url = $(e.currentTarget).data('url');

        if ($('li', items).length <= 1) {
            $('<li><i class="fa fa-spin fa-spinner"></i></li>').appendTo(items);

            $.get(url, (json) => {
                items.html(json.html);
                this.store(json.unread);

                // default max height of alerts area
                let maxHeight = 420;

                if (parseInt(Session.getItem('box-notify-h'))) {
                    // min height is 190px. max height is (one item height * number of items)
                    maxHeight = Math.min(items.height(), Math.max(190, parseInt(Session.getItem('box-notify-h'))));
                }

                this._modal.css('max-height', maxHeight); // max wysokosc obszaru powiadomien

                // on mobile devices width must be set on 100%
                if (parseInt(Session.getItem('box-notify-w'))  && $(window).width() > 768) {
                    this._dropdown.width(parseInt(Session.getItem('box-notify-w')));
                }

                require.ensure([], (require) => {
                    require('perfect-scrollbar/jquery')($);

                    this._modal.perfectScrollbar({suppressScrollX: true}).on('ps-y-reach-end', {url: url}, this._onScroll);
                });
            });
        }

        e.preventDefault();
    }

    /**
     * Blick on notification.
     *
     * @param e
     * @return {boolean}
     * @private
     */
    _onItemClick(e) {
        let $this = $(e.currentTarget);

        $this.parent().removeClass('unread');
        $this.attr('href', $this.data('url'));

        return false;
    }

    /**
     * Mark notification as read.
     *
     * @param e
     * @return {boolean}
     * @private
     */
    _onMarkClick(e) {
        $('li', this._self).removeClass('unread');
        this.store(0);

        $.post($(e.currentTarget).attr('href'));

        return false;
    }

    /**
     * Delete notification.
     *
     * @param e
     * @return {boolean}
     * @private
     */
    _onDeleteClick(e) {
        let $this = $(e.currentTarget);

        $.post($this.attr('href'));
        $this.parent().fadeOut();

        return false;
    }

    /**
     * Infinity scroll.
     *
     * @param e
     * @private
     */
    _onScroll(e) {
        let items = $(e.currentTarget).find('ul');

        $.get(e.data.url + '?offset=' + $('li', items).length, json => {
            items.append(json.html);

            if ($('li', json.html).length < 10) {
                $(e.currentTarget).off('ps-y-reach-end');
            }
        });
    }
}

$(function () {
    'use strict';

    let notifications = new Notifications();

    Session.addListener(function (e) {
        if (e.key === 'alerts' && e.newValue !== e.oldValue) {
            notifications.set(e.newValue);
            notifications.clear();
        }
    });

    ws.on('alert', data => {
        notifications.store(notifications.get() + 1);
        notifications.clear();

        DesktopNotifications.doNotify(data.headline, data.subject, data.url);
    });

    setInterval(() => {
        $.get(_config.ping, token => {
            $('meta[name="csrf-token"]').attr('content', token);
            $(':hidden[name="_token"]').val(token);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        });
    }, 350000);
});

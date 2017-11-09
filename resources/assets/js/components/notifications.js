import DesktopNotifications from '../libs/notifications';
import Session from '../libs/session';
import Config from '../libs/config';

/**
 * Manager alerts counter (set, get or clear)
 */
class Notifications
{
    constructor() {
        this._counter = 0;
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
     * @param counter
     */
    set(counter) {
        this._counter = Math.max(0, parseInt(counter));

        if (this._counter > 0) {
            this._setTitle('(' + (this._counter) + ') ' + this._pageTitle);
            this._setIcon(Config.cdn(`/img/xicon/favicon${Math.min(this._counter, 6)}.png`));
        } else {
            this._setTitle(this._pageTitle);
            this._setIcon(Config.cdn('/img/favicon.png'));
        }

        this._setBadge();
    }

    /**
     * Set alerts counter and save it in local storage.
     *
     * @param counter
     */
    store(counter) {
        this.set(counter);

        Session.setItem('notifications', this._counter);
    }

    /**
     * Get notifications counter
     *
     * @returns {Number|number}
     */
    get() {
        return this._counter;
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
     * @private
     */
    _setBadge() {
        let badge = $('.badge', this._self);

        if (this._counter === 0) {
            badge.remove();
        }
        else {
            if (!badge.length) {
                $('> a:first', this._self).prepend(`<span class="badge">${this._counter}</span>`);
            } else {
                badge.text(this._counter);
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
        $('head link[rel="shortcut icon"]').attr('href', url);
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
     * Click on notification.
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
        if (this._isRequestOngoing) {
            return;
        }

        let items = $(e.currentTarget).find('ul');
        this._isRequestOngoing = true;

        $.get(e.data.url + '?offset=' + $('li', items).length, json => {
            items.append(json.html);

            if (json.count < 10) {
                $(e.currentTarget).off('ps-y-reach-end');
            }

            this._isRequestOngoing = false;
        });
    }
}

$(function () {
    'use strict';

    let notifications = new Notifications();

    Session.addListener(function (e) {
        if (e.key === 'notifications' && e.newValue !== notifications.get()) {
            notifications.set(e.newValue);
            notifications.clear();
        }
    });

    ws.on('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', data => {
        notifications.set(notifications.get() + 1);
        notifications.clear();

        DesktopNotifications.doNotify(data.headline, data.subject, data.url);

        // ugly hack to firefox: store counter in session storage with a lille bit of delay.
        // if we have two open tabs both with websocket connection, then each tab will receive it's own
        // notification. saving counter in local storage will call session listener (see above).
        // make a long story short: without setTimeout() one notification will be shown as two if user
        // has two open tabs.
        setTimeout(() => Session.setItem('notifications', notifications.get()), 500);
    });

    setInterval(() => {
        // send ping request to the server just to extend session cookie lifetime.
        $.get(Config.get('ping'), token => {
            $('meta[name="csrf-token"]').attr('content', token);
            $(':hidden[name="_token"]').val(token);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        });
    }, Config.get('ping_interval') * 60 * 1000);
});

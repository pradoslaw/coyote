import DesktopNotifications from '../libs/notifications';

class Pm
{
    constructor() {
        this._self = $('#btn-messages');
        this._dropdown = $('#dropdown-messages');

        this._self.find('a[data-toggle="dropdown"]').click(this._onDropdownClick.bind(this));
    }

    /**
     * Get pm counter
     *
     * @returns {Number|number}
     */
    get() {
        return parseInt(this._self.find('.badge').text()) || 0;
    }

    /**
     * Set unread pm counter.
     *
     * @param value
     */
    set(value) {
        this._setBadge(value);
    }

    /**
     * Update counter badge.
     *
     * @param value
     * @private
     */
    _setBadge(value) {
        let badge = $('.badge', this._self);

        if (parseInt(value) === 0) {
            badge.remove();
        }
        else {
            if (!badge.length) {
                $('> a:first', this._self).prepend(`<span class="badge">${value}</span>`);
            } else {
                badge.text(value);
            }
        }
    }

    _onDropdownClick(e) {
        let items = this._dropdown.find('ul');

        if ($('li', items).length <= 1) {
            $.ajax({
                url: $(e.currentTarget).data('url'),
                success: html => items.html(html),
                cache: false
            });
        }

        e.preventDefault();
    }
}

$(function() {
    let pm = new Pm();

    ws.on('pm', data => {
        DesktopNotifications.doNotify(data.senderName, data.excerpt, '#top');

        pm.set(pm.get() + 1);
    });
});

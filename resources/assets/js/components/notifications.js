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
            var notification = new Notification(title, {body: body, icon: baseUrl + 'template/img/favicon.png'});

            notification.onshow = function () {
                setTimeout(function () {
                    notification.close()
                }, 5000);
            };

            return true;
        }
        return false;
    }
};
class DesktopNotifications {
  static requestPermission() {
    if (this._isSupported() && Notification.permission !== 'granted') {
      Notification.requestPermission(function (status) {
        if (Notification.permission !== status) {
          Notification.permission = status;
        }
      });
    }
  }

  static isAllowed() {
    return this._isSupported() && Notification.permission === "granted";
  }

  static doNotify(title, body, url) {
    if (this.isAllowed()) {
      let notification = new Notification(title, {body: body, tag: url, icon: '/img/favicon.png'});

      notification.onshow = () => setTimeout(() => notification.close(), 5000);
      notification.onclick = function () {
        window.open(url);
      };

      return true;
    }

    return false;
  }

  static _isSupported() {
    return ('Notification' in window && window['Notification'] !== null);
  }
}

module.exports = DesktopNotifications;

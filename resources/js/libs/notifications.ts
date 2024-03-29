export default class DesktopNotifications {
  static requestPermission(): Promise<NotificationPermission> {
    return Notification.requestPermission();
  }

  static get isDefault(): boolean {
    return Notification.permission === 'default';
  }

  static get isAllowed(): boolean {
    return Notification.permission === 'granted';
  }

  static get isSupported(): boolean {
    return ('Notification' in window && window['Notification'] !== null);
  }

  static notify(title: string, body: string, url: string) {
    if (!this.isSupported || !this.isAllowed) {
      return false;
    }

    try {
      const notification = new Notification(title, {body: body, tag: url, icon: '/img/favicon.png'});

      notification.onshow = () => setTimeout(() => notification.close(), 5000);
      notification.onclick = function () {
        window.open(url);
      };
    } catch (err) {
      console.error(err);

      // notifications may not work on some mobile devices
      return false;
    }

    return true;
  }
}


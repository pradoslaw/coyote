/**
 * Handle push event and display notification
 */
self.addEventListener('push', function(event) {
  if (!self.Notification) {
  // if (!(self.Notification && self.Notification.permission === 'granted')) {
    return;
  }

  const data = event.data.json();

  event.waitUntil(self.registration.showNotification(data.title, data));
});

self.addEventListener('notificationclick', (event) => {
  self.clients.openWindow(event.notification.data.url);
});

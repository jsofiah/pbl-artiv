self.addEventListener('push', (event) => {
  const d = event.data ? event.data.json() : {};
  event.waitUntil(
    self.registration.showNotification(d.title || 'Artiv', {
      body: d.body,
      icon: d.icon,
      badge: d.icon,
      data: d.data || {},
      tag: d.tag,
      renotify: !!d.tag,
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = event.notification.data?.url || '/';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((list) => {
      for (const c of list) {
        if ('focus' in c && 'navigate' in c) {
          return c.focus().then(() => c.navigate(url));
        }
      }
      return clients.openWindow(url);
    })
  );
});
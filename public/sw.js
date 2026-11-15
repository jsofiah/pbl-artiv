self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
  console.log('[SW] push event diterima', event.data ? event.data.text() : '(kosong)');

  let d = {};
  try {
    d = event.data ? event.data.json() : {};
  } catch (e) {
    console.error('[SW] json parse error', e);
    d = { title: 'Artiv', body: event.data ? event.data.text() : '' };
  }

  const options = {
    body: d.body || '',
    data: d.data || {},
  };

  if (d.icon) {
    options.icon = d.icon;
    options.badge = d.icon;
  }

  if (d.tag) {
    options.tag = String(d.tag);
    options.renotify = true;
  }

  event.waitUntil(
    self.registration
      .showNotification(d.title || 'Artiv', options)
      .then(() => console.log('[SW] showNotification sukses'))
      .catch((e) => console.error('[SW] showNotification gagal', e))
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = event.notification.data?.url || '/';
  const target = new URL(targetUrl, self.location.origin);

  event.waitUntil(
    self.clients
      .matchAll({ type: 'window', includeUncontrolled: true })
      .then((clientList) => {
        for (const client of clientList) {
          const clientUrl = new URL(client.url);
          if (clientUrl.origin === target.origin) {
            if ('focus' in client) {
              return client.focus().then(() => {
                if ('navigate' in client) {
                  return client.navigate(target.href);
                }
              });
            }
          }
        }
        return self.clients.openWindow(target.href);
      })
  );
});
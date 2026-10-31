const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

function urlBase64ToUint8Array(b64) {
  const pad = '='.repeat((4 - (b64.length % 4)) % 4);
  const raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
  return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}

export async function subscribeUserToPush() {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) return false;

  const permission = await Notification.requestPermission();
  if (permission !== 'granted') return false;

  const key = document.querySelector('meta[name="vapid-public-key"]')?.content;
  if (!key) return false;

  const reg = await navigator.serviceWorker.register('/sw.js');
  await navigator.serviceWorker.ready;

  const sub = await reg.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(key),
  });

  const res = await fetch('/push-subscriptions', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf(),
      Accept: 'application/json',
    },
    body: JSON.stringify({
      ...sub.toJSON(),
      contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
    }),
  });

  return res.ok;
}

export async function unsubscribeFromPush() {
  const reg = await navigator.serviceWorker.getRegistration('/sw.js');
  const sub = await reg?.pushManager.getSubscription();
  if (!sub) return true;

  await fetch('/push-subscriptions', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf(),
      Accept: 'application/json',
    },
    body: JSON.stringify({ endpoint: sub.endpoint }),
  });
  return sub.unsubscribe();
}

window.subscribeUserToPush = subscribeUserToPush;
window.unsubscribeFromPush = unsubscribeFromPush;
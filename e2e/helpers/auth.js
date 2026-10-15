import { expect } from '@playwright/test';

// Helper + selector terpusat untuk alur Auth & Profil.
// Kalau ada selector yang tidak cocok dengan Blade-mu, cukup ubah di sini.

export const URL = {
  login: '/login',
  register: '/register',
  beranda: '/',
  profil: '/profile', // <- cek dengan: php artisan route:list --name=profile
  pesananSaya: '/customer/pesanan',
  halamanCustomer: '/customer/beranda', // <- cek: php artisan route:list --name=customer.beranda
};

export const USER = {
  customer: {
    email: process.env.CUSTOMER_EMAIL || 'customer1@gmail.com',
    password: process.env.CUSTOMER_PASSWORD || 'customer123',
  },
  designer: {
    email: process.env.DESIGNER_EMAIL || 'designer1@gmail.com',
    password: process.env.DESIGNER_PASSWORD || 'designer123',
  },
  admin: {
    email: process.env.ADMIN_EMAIL || 'admin1@gmail.com',
    password: process.env.ADMIN_PASSWORD || 'admin123',
  },
};

// Semua locator ada di sini. Idealnya tambahkan data-testid di Blade.
export const SEL = {
  // login & register
  // id diambil dari Blade login/register (#email, #password, #password_confirmation)
  email: (page) => page.locator('#email'),
  password: (page) => page.locator('#password'),
  konfirmasiPassword: (page) => page.locator('#password_confirmation'),
  // Pakai atribut name: di halaman profil, <label> tidak punya for= sehingga getByLabel tidak bekerja
  namaLengkap: (page) => page.locator('input[name="full_name"]'),
  username: (page) => page.locator('input[name="username"]'),
  btnMasuk: (page) => page.getByRole('button', { name: /^masuk$/i }),
  btnDaftar: (page) => page.getByRole('button', { name: /^daftar$/i }),
  btnGoogle: (page) => page.getByRole('link', { name: /google/i }),

  // user menu & logout
  // Trigger dropdown user (x-shared.user-menu): tombol di <nav> yang memuat label role.
  // Lebih stabil kalau ditambah data-testid="user-menu" di tombolnya.
  userMenu: (page) => page.locator('nav button').filter({ hasText: /customer|designer|admin/i }),
  btnLogout: (page) =>
    page.getByRole('button', { name: /logout|keluar/i }),

  // edit profil (id/name diambil dari profile/edit.blade.php)
  noTelepon: (page) => page.locator('input[name="phone"]'),
  passwordLama: (page) => page.locator('#current_password'),
  passwordBaru: (page) => page.locator('#new_password'),
  konfirmasiBaru: (page) => page.locator('#new_password_confirmation'),
  inputFoto: (page) => page.locator('input[name="avatar"]'), // hidden, auto-submit saat dipilih
  btnSimpan: (page) => page.getByRole('button', { name: /simpan perubahan/i }),
  btnPerbaruiPassword: (page) => page.getByRole('button', { name: /perbarui password/i }),
  btnHapusFoto: (page) => page.getByRole('button', { name: /hapus\s+foto/i }),
  pesanError: (page) => page.locator('p.text-red-600'),

  // notifikasi (partials/notification-dropdown.blade.php)
  bell: (page) => page.locator('#notif-toggle'),
  bellDot: (page) => page.locator('#notif-toggle > span'), // titik kuning, ada bila ada notif belum dibaca
  panelNotif: (page) => page.locator('#notif-panel'),
  pillBaru: (page) => page.locator('#notif-panel span.rounded-full').filter({ hasText: /\d+\s*Baru/ }),
  btnTandaiDibaca: (page) => page.locator('#notif-panel').getByRole('button', { name: /tandai dibaca/i }),
  itemNotif: (page) => page.locator('#notif-panel a[href*="notifikasi"]'),
  itemBelumDibaca: (page) => page.locator('#notif-panel a[href*="notifikasi"][class*="bg-violet-50/60"]'),
  notifKosong: (page) => page.getByText('Belum ada notifikasi.'),
};

export async function login(page, { email, password }) {
  await page.goto(URL.login);
  await SEL.email(page).fill(email);
  await SEL.password(page).fill(password);
  await SEL.btnMasuk(page).click();
}

export async function loginAsCustomer(page) {
  await page.goto(URL.login);
  await SEL.email(page).fill(USER.customer.email);
  await SEL.password(page).fill(USER.customer.password);
  await SEL.btnMasuk(page).click();

  await expect(page).not.toHaveURL(/\/login(?:\?.*)?$/);
}

// Input Artiv memakai atribut HTML `required` dan type="email", jadi form
// diblokir oleh validasi browser (tooltip) sebelum sampai ke server.
// Tooltip tidak bisa dibaca Playwright, tapi status validity-nya bisa:
// flag = 'valueMissing' (kosong) atau 'typeMismatch' (format email salah)
export async function expectBrowserInvalid(locator, flag) {
  const invalid = await locator.evaluate((el, f) => el.validity[f], flag);
  expect(invalid).toBe(true);
}

// Email unik supaya register tidak bentrok saat test dijalankan berulang
export function uniqueEmail(prefix = 'johndoe') {
  return `${prefix}.${Date.now()}@gmail.com`;
}

// File palsu di memori untuk test upload
export function fakeFile(name, mimeType, sizeBytes) {
  return { name, mimeType, buffer: Buffer.alloc(sizeBytes, 1) };
}
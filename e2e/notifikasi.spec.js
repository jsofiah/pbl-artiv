import { test, expect } from '@playwright/test';
import { SEL, loginAsCustomer } from './helpers/auth.js';

// Notifikasi di Artiv = dropdown lonceng di navbar (partials/notification-dropdown),
// bukan halaman terpisah. Isinya diambil dari tabel `notifications`.
//
// PERSIAPAN DATA: akun customer1 perlu minimal 3 notifikasi BELUM dibaca.
// Jalankan sebelum test (php artisan tinker):
//
//   $u = App\Models\User::where('email','customer1@gmail.com')->first();
//   foreach ([['new_chat','Pesan baru','Halo, ini hasil desainnya'],
//             ['order_taken','Pesanan diambil','Desainer mengambil pesananmu'],
//             ['order_completed','Pesanan selesai','Pesananmu telah selesai']] as [$t,$ti,$b])
//       App\Models\Notification::create(['user_id'=>$u->id,'type'=>$t,'title'=>$ti,'body'=>$b,'data'=>['sender_name'=>'Sofiah']]);
//
// Test NF-008 dan NF-009 menandai notifikasi sebagai dibaca, jadi data
// harus di-seed ulang tiap kali test dijalankan.

async function jumlahBelumDibaca(page) {
  const pill = SEL.pillBaru(page);
  if ((await pill.count()) === 0) return 0;
  const teks = (await pill.first().textContent()) ?? '';
  return parseInt(/(\d+)/.exec(teks)?.[1] ?? '0', 10);
}

test.describe.configure({ mode: 'serial' });

test.describe('Notifikasi (S12.1 - S12.2)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsCustomer(page);
  });

  test('TC-NF-001 Customer dapat membuka notifikasi lewat ikon lonceng', async ({ page }) => {
    await expect(SEL.panelNotif(page)).toBeHidden();
    await SEL.bell(page).click();
    await expect(SEL.panelNotif(page)).toBeVisible();
    await expect(SEL.panelNotif(page).getByRole('heading', { name: /notifikasi/i })).toBeVisible();
  });

  test('TC-NF-002 Daftar notifikasi ditampilkan', async ({ page }) => {
    await SEL.bell(page).click();
    await expect(SEL.panelNotif(page)).toBeVisible();
    if ((await SEL.itemNotif(page).count()) > 0) {
      await expect(SEL.itemNotif(page).first()).toBeVisible();
      await expect(SEL.itemNotif(page).first().locator('p').first()).not.toBeEmpty(); // judul
    } else {
      await expect(SEL.notifKosong(page)).toBeVisible();
    }
  });

  test('TC-NF-003 Notifikasi belum dibaca ditandai', async ({ page }) => {
    await SEL.bell(page).click();
    test.skip((await SEL.itemBelumDibaca(page).count()) === 0, 'Seed notifikasi belum dibaca dulu');
    await expect(SEL.itemBelumDibaca(page).first()).toBeVisible();
    await expect(SEL.pillBaru(page)).toBeVisible(); // pill "N Baru"
    await expect(SEL.bellDot(page)).toBeVisible();   // titik kuning di lonceng
  });

  // TC-NF-004 s/d 007 butuh event nyata dari role lain (Designer kirim chat,
  // order diambil, revisi, approval). Uji manual dengan dua browser + screenshot.
  test.fixme('TC-NF-004 Notifikasi chat masuk (butuh Designer)', async () => {});
  test.fixme('TC-NF-005 Notifikasi order baru', async () => {});
  test.fixme('TC-NF-006 Notifikasi revisi (butuh Designer)', async () => {});
  test.fixme('TC-NF-007 Notifikasi approval', async () => {});

  test('TC-NF-008 Notifikasi dapat ditandai sudah dibaca (klik item)', async ({ page }) => {
    await SEL.bell(page).click();
    const sebelum = await jumlahBelumDibaca(page);
    test.skip(sebelum === 0, 'Seed notifikasi belum dibaca dulu');

    await SEL.itemBelumDibaca(page).first().click(); // route customer.notifikasi.read lalu redirect
    await page.waitForLoadState('load');
    await SEL.bell(page).click();
    expect(await jumlahBelumDibaca(page)).toBe(sebelum - 1);
  });

  test('TC-NF-009 Jumlah penanda belum dibaca diperbarui (Tandai dibaca)', async ({ page }) => {
    await SEL.bell(page).click();
    test.skip((await jumlahBelumDibaca(page)) === 0, 'Seed notifikasi belum dibaca dulu');

    await SEL.btnTandaiDibaca(page).click();
    await page.waitForLoadState('load');
    await SEL.bell(page).click();
    expect(await jumlahBelumDibaca(page)).toBe(0);
    await expect(SEL.bellDot(page)).toHaveCount(0);
  });
});

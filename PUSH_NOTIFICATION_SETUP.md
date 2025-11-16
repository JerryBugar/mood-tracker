# Setup Push Notification dengan Laravel WebPush

## Catatan Penting

Implementasi push notification sudah selesai. Untuk mengaktifkan push notification, Anda perlu melakukan setup VAPID keys.

## Setup VAPID Keys

Karena generate otomatis VAPID keys gagal (kemungkinan karena OpenSSL di Windows), Anda perlu generate VAPID keys secara manual.

### Cara 1: Menggunakan Online Tool

1. Kunjungi: https://web-push-codelab.glitch.me/
2. Klik "Generate VAPID Keys"
3. Copy **Public Key** dan **Private Key**
4. Tambahkan ke file `.env`:

```env
VAPID_SUBJECT=mailto:your-email@example.com
VAPID_PUBLIC_KEY=your-public-key-here
VAPID_PRIVATE_KEY=your-private-key-here
```

### Cara 2: Menggunakan Node.js

Jika Anda memiliki Node.js terinstall:

```bash
npm install -g web-push
web-push generate-vapid-keys
```

Copy hasil Public Key dan Private Key ke `.env`.

### Cara 3: Menggunakan PHP (jika OpenSSL support)

Jika OpenSSL di server Anda support, jalankan:

```bash
php artisan webpush:vapid
```

## Konfigurasi .env

Tambahkan konfigurasi berikut di file `.env`:

```env
VAPID_SUBJECT=mailto:admin@ceremood.com
VAPID_PUBLIC_KEY=your-public-key-here
VAPID_PRIVATE_KEY=your-private-key-here
```

**Catatan:**
- `VAPID_SUBJECT` harus berupa email atau URL (format: `mailto:email@example.com`)
- `VAPID_PUBLIC_KEY` dan `VAPID_PRIVATE_KEY` harus di-generate secara manual

## Testing Push Notification

1. Pastikan VAPID keys sudah di-setup di `.env`
2. Login sebagai user yang sudah verified
3. Buka halaman `/notif`
4. Aktifkan toggle "Push Notification"
5. Berikan permission browser untuk push notification
6. Dari admin panel, kirim notifikasi test
7. Push notification akan muncul di browser/device user

## Fitur yang Sudah Diimplementasikan

✅ Install package Laravel WebPush
✅ Migration untuk tabel push_subscriptions
✅ Backend endpoints untuk subscribe/unsubscribe
✅ Frontend JavaScript untuk handle subscription
✅ Service Worker untuk handle push notification
✅ UI toggle di halaman notifikasi
✅ Integrasi dengan sistem notifikasi yang ada
✅ Push notification untuk notifikasi langsung dan scheduled

## Troubleshooting

### Push notification tidak muncul
- Pastikan VAPID keys sudah di-setup dengan benar
- Pastikan browser mendukung push notification
- Pastikan user sudah memberikan permission
- Cek console browser untuk error
- Pastikan service worker sudah terdaftar

### Error saat subscribe
- Pastikan VAPID public key ada di meta tag (cek di source HTML)
- Pastikan service worker sudah terdaftar
- Cek console browser untuk error detail

### Push notification tidak terkirim
- Pastikan VAPID keys valid
- Cek log Laravel untuk error
- Pastikan user sudah subscribe
- Pastikan notifikasi sudah di-attach ke user


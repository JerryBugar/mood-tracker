# Dokumentasi PWA (Progressive Web App)

Implementasi PWA telah ditambahkan ke aplikasi Mood Tracker. Dokumen ini menjelaskan fitur-fitur yang telah diimplementasikan.

## Fitur yang Diimplementasikan

### 1. Web App Manifest (`public/manifest.json`)
- Konfigurasi aplikasi untuk instalasi sebagai PWA
- **Start URL**: `/dashboard` - Aplikasi dimulai dari dashboard
- Ikon aplikasi
- Theme color dan background color
- Display mode: standalone
- **Shortcuts** untuk navigasi cepat ke:
  - Dashboard
  - Home
  - Calendar
  - Notifikasi
  - Profile

### 2. Service Worker (`public/sw.js`)
- **Caching Strategy**: Cache-first dengan fallback ke network
- **Static Assets Caching**: Cache halaman utama saat install:
  - `/dashboard` - Halaman dashboard
  - `/auth/verify` - Halaman verifikasi
  - `/home` - Halaman utama
  - `/calendar` - Halaman kalender
  - `/notif` - Halaman notifikasi
  - `/profile` - Halaman profil
- **Runtime Caching**: Cache halaman-halaman utama saat runtime dengan auto-update di background
- **Offline Support**: Menampilkan halaman offline saat tidak ada koneksi
- **Background Sync**: Siap untuk sync data saat online kembali
- **Push Notifications**: Siap untuk notifikasi push (optional)

### 3. Install Prompt (`public/js/pwa.js`)
- Deteksi install prompt dari browser
- Tombol install otomatis muncul
- Update notification saat ada versi baru
- Deteksi standalone mode

### 4. Offline Page (`public/offline.html`)
- Halaman khusus untuk kondisi offline
- UI yang user-friendly dengan tombol retry

### 5. Meta Tags PWA
Ditambahkan di layout `app.blade.php` dan `admin.blade.php`:
- `theme-color`: Warna tema aplikasi
- `apple-mobile-web-app-capable`: Enable standalone mode di iOS
- `apple-mobile-web-app-status-bar-style`: Style status bar iOS
- `apple-mobile-web-app-title`: Nama aplikasi di iOS
- `apple-touch-icon`: Ikon untuk iOS
- `manifest`: Link ke manifest.json

## Cara Menggunakan

### Testing PWA

1. **Chrome DevTools**:
   - Buka Chrome DevTools (F12)
   - Tab "Application" > "Service Workers"
   - Tab "Application" > "Manifest"
   - Tab "Lighthouse" > Run audit untuk PWA

2. **Install Aplikasi**:
   - Di desktop: Icon install akan muncul di address bar
   - Di mobile: Menu "Add to Home Screen" akan muncul
   - Tombol install juga akan muncul di aplikasi (bottom right)

3. **Testing Offline**:
   - Buka Chrome DevTools (F12)
   - Tab "Network" > Pilih "Offline"
   - Refresh halaman untuk melihat offline page

### Update Service Worker

Saat ada update service worker:
1. Ubah `CACHE_NAME` di `public/sw.js` (misalnya: `mood-tracker-v2`)
2. Service worker akan otomatis update dan notifikasi akan muncul
3. User dapat klik "Update Sekarang" untuk reload aplikasi

## File yang Dibuat/Dimodifikasi

### File Baru:
- `public/manifest.json` - Web App Manifest
- `public/sw.js` - Service Worker
- `public/offline.html` - Halaman offline
- `public/js/pwa.js` - JavaScript untuk PWA functionality

### File yang Dimodifikasi:
- `resources/views/layouts/app.blade.php` - Menambahkan meta tags PWA
- `resources/views/layouts/admin.blade.php` - Menambahkan meta tags PWA
- `routes/web.php` - Menambahkan route untuk offline page

## Konfigurasi

### Mengubah Theme Color
Edit `theme-color` di:
- `resources/views/layouts/app.blade.php` (line 11)
- `resources/views/layouts/admin.blade.php` (line 12)
- `public/manifest.json` (line 8)

### Menambahkan Assets ke Cache
Edit array `STATIC_ASSETS` di `public/sw.js` (line 5-14)

### Mengubah Nama Aplikasi
Edit di `public/manifest.json`:
- `name`: Nama lengkap aplikasi
- `short_name`: Nama pendek aplikasi

## Catatan Penting

1. **HTTPS Required**: PWA memerlukan HTTPS di production (kecuali localhost)
2. **Service Worker Scope**: Service worker hanya bekerja di scope root (`/`)
3. **Cache Management**: Cache akan otomatis dihapus saat service worker update
4. **Browser Support**: 
   - Chrome/Edge: Full support
   - Firefox: Full support
   - Safari iOS: Partial support (beberapa fitur terbatas)

## Troubleshooting

### Service Worker Tidak Terdaftar
- Pastikan aplikasi dijalankan via HTTPS atau localhost
- Cek console browser untuk error
- Pastikan file `sw.js` dapat diakses di `/sw.js`

### Install Prompt Tidak Muncul
- Pastikan manifest.json valid
- Pastikan service worker terdaftar
- Pastikan aplikasi belum diinstall sebelumnya
- Beberapa browser memerlukan interaksi user terlebih dahulu

### Offline Page Tidak Muncul
- Pastikan `offline.html` ada di `public/`
- Pastikan route `/offline` sudah ditambahkan
- Cek service worker cache untuk `/offline.html`

## Referensi

- [MDN: Progressive Web Apps](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [Web.dev: PWA](https://web.dev/progressive-web-apps/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)


# Honey's Bite

Honey's Bite adalah aplikasi web untuk penjualan dan pengelolaan inventaris bakery. Pelanggan dapat melihat katalog, membuat pesanan, melacak status, dan memberi ulasan yang memenuhi syarat. Admin dapat mengelola produk dan pesanan serta melihat ringkasan operasional.

## Fitur yang tersedia

### Pelanggan

- Registrasi, login, dan logout.
- Melihat katalog, detail produk, dan ulasan produk.
- Membuat pesanan pickup atau delivery dengan harga serta ongkir dihitung di server.
- Melihat riwayat dan detail pesanan.
- Mengirim satu ulasan per produk setelah memiliki pesanan produk tersebut berstatus `Selesai`.
- Melakukan simulasi pembayaran transfer bank atau QRIS demo.

### Admin

- Dashboard dengan jumlah produk aktif, total pesanan, pesanan menunggu konfirmasi, penjualan yang berstatus `Dibayar`, dan produk aktif dengan stok lima atau kurang.
- Menambah, mengubah, mengarsipkan, dan melihat produk.
- Meninjau, mengonfirmasi, menolak, dan menyelesaikan pesanan; stok dialokasikan saat konfirmasi.
- Mengelola ongkir delivery tetap. Nilai ongkir pada pesanan yang sudah dibuat tidak berubah.
- Menandai pembayaran COD yang diterima.

**Catatan pembayaran:** transfer bank dan QRIS pada aplikasi ini hanya simulasi. Tidak ada transaksi uang, koneksi bank, payment gateway, atau QR pembayaran sungguhan.

## Teknologi

- PHP 8.3 atau versi yang memenuhi constraint Composer project (`^8.3`).
- Laravel Framework 13 (`^13.17` pada `composer.json`).
- Composer 2.x.
- MySQL untuk penggunaan aplikasi; konfigurasi project juga menyediakan SQLite.
- Node.js dan npm jika ingin menjalankan toolchain frontend. `package.json` menggunakan Vite 8; gunakan versi Node.js yang didukung Vite 8 (misalnya Node.js 20.19+ atau 22.12+).
- npm dan Vite 8, Tailwind CSS 4.
- Blade, CSS, dan JavaScript.

Project tidak mengunci versi server MySQL tertentu. Gunakan versi MySQL yang didukung versi Laravel/PDO MySQL pada lingkungan Anda, dan pastikan ekstensi PHP `pdo_mysql` aktif.

`composer.lock` mengunci dependency PHP. Saat ini belum ada npm lockfile, sehingga versi dependency frontend yang dipasang melalui rentang pada `package.json` tidak terkunci persis.

## Persiapan lokal dengan Windows/Laragon

Versi menu Laragon dapat berbeda. Pastikan PHP CLI yang dipakai terminal adalah PHP 8.3 atau yang kompatibel, MySQL aktif, serta perintah `php`, `composer`, `node`, dan `npm` tersedia di terminal.

### 1. Pasang dependency

Dari direktori project:

```powershell
composer install
npm install
```

**Perhatian:** `composer run setup` menjalankan `php artisan key:generate` dan `php artisan migrate --force` secara otomatis setelah menyiapkan `.env` (jika belum ada). `key:generate` dapat mengganti `APP_KEY` yang sudah tersimpan; menggantinya pada aplikasi yang sudah digunakan dapat membuat data terenkripsi atau sesi lama tidak lagi dapat dibaca. Opsi migration `--force` melewati konfirmasi perlindungan environment; migration berjalan pada database yang dipilih oleh `.env` dan dapat mengubah schema/data. Jangan jalankan script tersebut sebelum memeriksa `.env`, memastikan database yang dituju aman, dan memahami dampak pergantian key. Panduan di bawah menggunakan langkah manual agar target database dapat diperiksa sebelum migration.

### 2. Siapkan environment dan database

Salin template environment jika `.env` belum ada:

```powershell
Copy-Item .env.example .env
```

Buat database kosong melalui MySQL/Laragon atau alat administrasi database yang Anda gunakan. Sesuaikan bagian database di `.env` dengan konfigurasi lokal Anda:

```dotenv
APP_NAME="Honey's Bite"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_lokal
DB_USERNAME=pengguna_mysql_lokal
DB_PASSWORD=password_mysql_lokal
```

Ganti nama database, pengguna, dan password dengan nilai lokal Anda; jangan menyalin kredensial ini ke repository. Jangan menimpa `.env` yang sudah berisi konfigurasi lokal.

Buat application key:

```powershell
php artisan key:generate
```

Pastikan koneksi dan target database yang dipilih benar sebelum menjalankan migration. Untuk database baru/lokal, jalankan:

```powershell
php artisan migrate
```

Jangan gunakan `migrate:fresh` pada database yang mungkin berisi data karena perintah tersebut menghapus tabel dan data.

### 3. Siapkan penyimpanan foto produk

Foto produk disimpan pada disk `public`. Buat symbolic link agar file dapat diakses aplikasi:

```powershell
php artisan storage:link
```

Jika perintah gagal karena symbolic link tidak diizinkan, jalankan terminal dengan hak yang sesuai atau ikuti konfigurasi filesystem Laragon/Windows Anda.

### 4. Jalankan aplikasi dan toolchain frontend

Jalankan server aplikasi pada satu terminal:

```powershell
php artisan serve
```

Pada terminal lain, Vite dapat dijalankan untuk development:

```powershell
npm run dev
```

Perintah berikut menghasilkan asset production:

```powershell
npm run build
```

Laragon dapat menyediakan virtual host sendiri; jika menggunakannya, atur `APP_URL` agar sesuai dan jalankan Vite secara terpisah saat mengembangkan asset.

Sebagai alternatif, `composer run dev` menjalankan proses development yang dikonfigurasi Laravel. Saat ini `php artisan dev:list` menampilkan server PHP, queue listener, dan Vite. Pastikan dependency kedua ekosistem sudah dipasang dan database lokal sudah disiapkan sebelum menjalankannya.

Konfigurasi Vite/Tailwind tersedia di `vite.config.js` dan `resources/css/app.css`, tetapi Blade views saat ini tidak memakai direktif `@vite`; styling halaman yang ada ditulis di dalam view dan `resources/js/app.js` belum berisi logika aplikasi. Karena itu, perintah Vite di atas menjalankan atau membangun toolchain yang tersedia, tetapi hasilnya belum dimuat oleh halaman Honey's Bite. Integrasi asset ke Blade perlu dilakukan terpisah sebelum mengandalkan output build untuk tampilan aplikasi.

## Pengujian

Test suite menggunakan konfigurasi SQLite in-memory pada `phpunit.xml`, sehingga tidak memerlukan database development:

```powershell
php artisan test
```

## Akses fitur lokal

- Halaman utama: `http://localhost:8000/`
- Katalog: `http://localhost:8000/catalog`
- Login dan registrasi: `/login` dan `/register`
- Riwayat pesanan pelanggan (setelah login): `/orders`
- Dashboard Admin (akun ber-role Admin): `/admin`
- Pengelolaan produk Admin: `/admin/products`
- Pengelolaan pesanan Admin: `/admin/orders`
- Pengaturan ongkir Admin: `/admin/settings/delivery-fee`

Registrasi publik membuat akun ber-role User; aplikasi tidak menyediakan akun Admin bawaan atau kredensial contoh. Akses Admin harus disiapkan secara terkontrol oleh pengelola lingkungan lokal melalui proses provisioning yang dipercaya. Jangan mempromosikan akun melalui endpoint publik.

## Keamanan repository

Jangan mengunggah `.env`, application key, password, token, kredensial database, kredensial layanan, atau secret lainnya ke repository publik. Gunakan `.env.example` sebagai template tanpa nilai rahasia, dan periksa perubahan sebelum mempublikasikannya.

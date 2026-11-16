# Toko Merchandise Sekolah

Fondasi aplikasi merchandise SMK TI Airlangga Samarinda dengan Laravel 13, Blade, Tailwind CSS, Vite, dan MySQL 8.

## Instalasi lokal

1. Siapkan PHP 8.3+, Composer, Node.js, dan MySQL 8. Buat database kosong beserta pengguna database dengan akses ke database tersebut.
2. Jalankan `composer install` dan `npm ci`.
3. Salin `.env.example` ke `.env`. Isi konfigurasi MySQL dan `ADMIN_EMAIL` serta `ADMIN_PASSWORD` untuk akun awal. Gunakan password minimal 12 karakter. Atur `STORE_SEED_DEMO=true` jika perlu katalog contoh lokal.
4. Jalankan `php artisan key:generate`, `php artisan migrate --seed`, `php artisan storage:link`, dan `npm run build`.
5. Jalankan `php artisan serve`. Buka http://127.0.0.1:8000/admin/login untuk masuk menggunakan akun admin hasil seeder.

Seeder membaca kredensial saat tabel pengguna kosong dan menyimpan password sebagai hash. Menjalankan ulang seeder tidak mengubah akun yang sudah ada. Simpan kredensial hanya di konfigurasi lokal.

## Panel admin

Setelah masuk, admin dapat melihat ringkasan stok, mengelola produk dan kategori, mengunggah foto, serta memperbarui pengaturan toko dan profil. Produk yang diarsipkan menggunakan soft delete. Dasbor menghitung stok dari jumlah stok semua varian per produk. Ambang stok menipis dapat diatur melalui STORE_LOW_STOCK_THRESHOLD (default 5 unit).

Untuk unggahan 10 foto sekaligus, siapkan upload_max_filesize minimal 4 MB dan post_max_size lebih dari 40 MB pada server PHP.

## Skema

- `categories` ke `products` merupakan relasi satu-ke-banyak. Kategori yang masih dirujuk produk dilindungi oleh foreign key.
- `products` memakai soft delete dan memiliki gambar di `product_images` serta varian di `product_variants`. Harga disimpan sebagai rupiah bulat. Harga varian yang kosong mengikuti harga dasar produk.
- `carts` memiliki UUID unik dan waktu kedaluwarsa yang diindeks. `cart_items` unik per pasangan keranjang–varian. Menghapus keranjang menghapus itemnya. Varian yang dirujuk item keranjang dilindungi oleh foreign key.
- `store_settings` diinisialisasi pada ID 1 oleh seeder. Nomor WhatsApp dan logo bisa kosong pada instalasi awal.
- Laravel juga membuat tabel untuk sesi, cache, dan antrean sesuai konfigurasi bawaan proyek.

Jalankan `php artisan test` untuk pengujian default dengan SQLite in-memory. Untuk pengujian MySQL, gunakan database kosong khusus pengujian dan set `DB_CONNECTION=mysql` serta `DB_DATABASE` ke nama database tersebut di environment proses pengujian. Suite memakai `RefreshDatabase`, yang dapat menghapus tabel pada database pengujian.


## Keranjang pengunjung

Keranjang disimpan di database dan dipulihkan melalui UUID di sesi Laravel. Masa berlaku default 72 jam diperbarui ketika isi keranjang berubah. Gunakan STORE_CART_LIFETIME_HOURS untuk mengatur masa berlaku di database. Pengunjung tetap memerlukan sesi browser yang menyimpan UUID keranjang.

Jalankan php artisan schedule:work untuk menjalankan pembersihan otomatis secara lokal. Di server, jalankan php artisan schedule:run setiap menit melalui penjadwal sistem. Perintah php artisan cart:prune dapat dijalankan langsung.

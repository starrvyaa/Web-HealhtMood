
# HealthMood

Versi ini sudah dibuat ulang dengan CSS biasa dan PHP sederhana agar bisa berjalan di XAMPP.

## Cara menjalankan

1. Copy folder `HealhtMood` ke `C:\xampp\htdocs\HealhtMood`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka `http://localhost/phpmyadmin`, lalu import file `database.sql`.
4. Buka `http://localhost/HealhtMood/index.php`.

## Login demo

- Email: `admin@healthmood.test`
- Password: `admin123`

## Fitur

- Header dan footer dibuat konsisten mengikuti contoh home page.
- Halaman menu dipisah: Home, Mood, Tidur, Laporan Mood, Laporan Tidur, Game.
- Login, register, dan logout.
- CRUD mood: tambah, edit, hapus.
- CRUD tidur: tambah, edit, hapus.
- Grafik home otomatis refresh dari database setiap 10 detik.
- Tambah data mood dan tidur memakai pop up.
- Logout memakai pop up konfirmasi.

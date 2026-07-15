SIM MAHASISWA - PROJECT UAS PHP MYSQL
=====================================

FITUR
- Login dan logout menggunakan session.
- Password aman menggunakan password_hash/password_verify.
- Dashboard dinamis dari database.
- CRUD data mahasiswa (tambah, tampil, edit, hapus).
- Pencarian, filter prodi/status, dan pagination.
- Validasi PHP dan JavaScript.
- Proteksi prepared statement, escaping output, dan CSRF token.
- Filter pilihan kelas berdasarkan program studi.
- Tampilan responsif Bootstrap dan sidebar mobile.
- Konfirmasi hapus, password toggle, counter karakter, dan alert otomatis.

CARA MENJALANKAN DI XAMPP
1. Salin folder "sim-mahasiswa-uas" ke:
   C:\xampp\htdocs\
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka http://localhost/phpmyadmin
4. Pilih menu Import, lalu impor:
   database/db_mahasiswa.sql
5. Buka aplikasi:
   http://localhost/sim-mahasiswa-uas/

AKUN LOGIN AWAL
Username : admin
Password : admin123

KONFIGURASI DATABASE
File: config/koneksi.php
Default XAMPP:
- Host: localhost
- Database: db_mahasiswa
- User: root
- Password: kosong

CATATAN
- File SQL akan membuat database dan tabel sesuai rancangan awal.
- Data mahasiswa tidak diisi dummy; tambahkan melalui menu Tambah Mahasiswa.
- Jika MySQL root menggunakan password, ubah DB_PASS pada config/koneksi.php.

# Survei Kepuasan Pasien RSPI

Aplikasi web PHP Native + MySQL untuk survei kepuasan pasien rumah sakit dengan struktur folder yang lebih rapi.

## Struktur Folder

- `config/koneksi.php` : koneksi database + helper aplikasi
- `auth/login.php` : form login admin + modal input user
- `auth/proses_login.php` : proses login
- `auth/simpan_user.php` : proses simpan user baru dari modal
- `auth/logout.php` : logout
- `admin/dashboard.php` : dashboard admin
- `admin/pertanyaan/` : CRUD pertanyaan
- `admin/hasil/` : hasil survei rawat jalan dan rawat inap
- `survey/` : form pasien dan proses simpan jawaban
- `assets/css/style.css` : stylesheet utama
- `database/survey_db.sql` : schema database
- `index.php` : landing page utama

## Cara Menjalankan

1. Buat database dengan nama `survei_rs`
2. Import file `database/survey_db.sql`
3. Pastikan setting database di `config/koneksi.php` sudah sesuai
4. Buka `http://localhost/survey-kepuasan/`

## Login Default

- Username: `admin`
- Password: `admin123`

## Fitur Tambahan Baru

- Modal input user di halaman login
- File proses simpan user dipisah di `auth/simpan_user.php`
- Struktur folder lebih terorganisir dan mudah dikembangkan

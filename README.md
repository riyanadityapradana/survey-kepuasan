# Survei Kepuasan Pasien RSPI

Aplikasi web `PHP Native + MySQL` untuk survei kepuasan pasien rumah sakit. Project ini menyediakan form survei `Rawat Jalan` dan `Rawat Inap`, dashboard admin, rekap hasil, modul komplain, dan notifikasi Telegram saat survei baru berhasil dikirim.

## Ringkasan Fitur

- Form survei pasien untuk `Rawat Jalan`
- Form survei pasien untuk `Rawat Inap`
- Penyimpanan responden dan jawaban ke database MySQL
- Dashboard admin dengan ringkasan dan grafik
- Rekap hasil survei dan detail responden
- Modul komplain manual dan komplain otomatis dari survei
- Notifikasi Telegram ke channel saat survei baru masuk

## Struktur Folder

- `config/koneksi.php`
  Bootstrap koneksi database, session, dan loader konfigurasi aplikasi.
- `.env`
  Menyimpan konfigurasi lokal seperti database dan token Telegram.
- `auth/login.php`
  Form login admin.
- `auth/proses_login.php`
  Proses login admin.
- `auth/simpan_user.php`
  Proses tambah user baru.
- `auth/logout.php`
  Logout admin.
- `admin/dashboard.php`
  Dashboard admin.
- `admin/pertanyaan/`
  CRUD pertanyaan survei.
- `admin/hasil/`
  Rekap hasil survei rawat jalan dan rawat inap.
- `admin/komplain/`
  Kelola komplain dan tindak lanjut.
- `survey/form_ralan.php`
  Form survei rawat jalan.
- `survey/form_ranap.php`
  Form survei rawat inap.
- `survey/simpan_jawaban.php`
  Proses simpan jawaban survei dan kirim notifikasi Telegram.
- `assets/css/style.css`
  Stylesheet utama.
- `database/survei_rs.sql`
  Schema database.
- `index.php`
  Landing page utama.

## Cara Menjalankan Project

1. Buat file `.env` dari `.env.example`.
2. Buat database dengan nama `survei_rs`.
3. Import file `database/survei_rs.sql` ke MySQL.
4. Sesuaikan konfigurasi database dan Telegram di file `.env`.
5. Jalankan Apache dan MySQL dari XAMPP.
6. Buka aplikasi di browser:

```text
http://localhost/survey-kepuasan/
```

## Login Default

- Username: `admin`
- Password: `admin123`

## Alur Data Survei

1. Pasien membuka form `Rawat Jalan` atau `Rawat Inap`.
2. Pasien mengisi identitas, memilih jawaban, dan mengisi saran jika ada.
3. Sistem menyimpan data ke tabel `responden` dan `jawaban`.
4. Jika ada saran atau jawaban `Tidak Puas`, sistem bisa membuat komplain otomatis.
5. Setelah data berhasil tersimpan, sistem mengirim notifikasi ke Telegram channel.

## Setup Notifikasi Telegram

Fitur ini digunakan agar setiap survei baru yang berhasil disimpan langsung mengirim pesan ke Telegram channel.

### 1. Cara Membuat Channel Telegram

1. Buka aplikasi Telegram.
2. Klik `New Message` atau ikon pensil.
3. Pilih `New Channel`.
4. Isi nama channel.
   Contoh: `FORM SURVEY RSPI`
5. Isi deskripsi channel jika perlu.
6. Pilih jenis channel:
   - `Public Channel` jika ingin punya username publik
   - `Private Channel` jika hanya untuk orang tertentu
7. Untuk project ini disarankan memakai `Public Channel` agar lebih mudah dipakai bot.
8. Buat username channel.
   Contoh:

```text
@form_survey_rspi
```

### 2. Cara Membuat Bot Telegram Melalui `@BotFather`

1. Cari akun Telegram resmi `@BotFather`.
2. Kirim perintah:

```text
/start
```

3. Buat bot baru dengan perintah:

```text
/newbot
```

4. Masukkan nama bot.
   Contoh:

```text
Survey RSPI Bot
```

5. Masukkan username bot, harus diakhiri kata `bot`.
   Contoh:

```text
survey_rspi_notif_bot
```

6. Setelah selesai, `@BotFather` akan memberikan `bot token`.

Contoh bentuk token:

```text
123456789:AAExampleTokenTelegramBot
```

Catatan penting:
- `bot token` bersifat rahasia.
- Jangan simpan token di screenshot yang dibagikan ke orang lain.
- Jika token terlanjur tersebar, gunakan `/revoke` di `@BotFather` untuk membuat token baru.

### 3. Cara Mendapatkan Token Bot Lagi Jika Lupa

1. Buka chat `@BotFather`.
2. Kirim:

```text
/revoke
```

3. Pilih bot yang ingin diganti token-nya.
4. `@BotFather` akan memberikan token baru.
5. Gunakan token baru tersebut di project.

### 4. Cara Menambahkan Bot ke Channel

1. Buka channel Telegram yang sudah dibuat.
2. Masuk ke pengaturan channel.
3. Pilih `Administrators`.
4. Tambahkan bot yang sudah dibuat.
5. Jadikan bot sebagai admin channel.

Untuk project ini, bot harus bisa mengirim pesan ke channel.

### 5. Channel Yang Dipakai di Project Ini

Project ini sudah disiapkan untuk mengirim notifikasi ke channel berikut:

```text
@form_survey_rspi
```

Jika nanti username channel berubah, ubah juga nilainya di file config project.

## Konfigurasi Telegram di Project

Konfigurasi utama sekarang memakai file:

- `.env`

Bagian yang dipakai untuk Telegram adalah:

```php
TELEGRAM_BOT_TOKEN=""
TELEGRAM_CHAT_ID="@form_survey_rspi"
```

### Cara Mengisi Token Bot

Buka file:

- `.env`

Lalu ubah:

```env
TELEGRAM_BOT_TOKEN=""
```

Menjadi seperti ini:

```env
TELEGRAM_BOT_TOKEN="ISI_TOKEN_BOT_KAMU_DI_SINI"
```

Contoh:

```env
TELEGRAM_BOT_TOKEN="123456789:AAExampleTokenTelegramBot"
```

Jika channel Telegram berubah, ubah juga bagian ini:

```env
TELEGRAM_CHAT_ID="@form_survey_rspi"
```

## Bagian Kode Yang Sudah Ditambahkan Untuk Telegram

Fitur notifikasi Telegram sudah dipasang di dua area utama.

### 1. File `config/app.php` dan `.env`

Di bagian ini digunakan:

- Konstanta Telegram:
  - `TELEGRAM_BOT_TOKEN`
  - `TELEGRAM_CHAT_ID`
  - `TELEGRAM_NOTIF_ENABLED`
- Helper label jenis survei:
  - `label_jenis_survei()`
- Helper ringkasan jawaban:
  - `ringkasan_nilai_survei()`
- Helper kirim pesan ke Telegram:
  - `kirim_pesan_telegram()`
- Helper format notifikasi survei:
  - `kirim_notifikasi_telegram_survei()`

Fungsi helper ini dipakai agar logika pengiriman Telegram tidak bercampur langsung dengan proses simpan data.

### 2. File `survey/simpan_jawaban.php`

Di file ini notifikasi Telegram dipanggil setelah data survei berhasil disimpan.

Alur di file ini sekarang:

1. Validasi input pasien
2. Simpan data ke tabel `responden`
3. Simpan data ke tabel `jawaban`
4. Sinkronisasi komplain otomatis
5. `commit` transaksi database
6. Kirim notifikasi Telegram
7. Redirect ke halaman terima kasih

Pemanggilan notifikasi dilakukan dengan fungsi:

```php
kirim_notifikasi_telegram_survei($jenis, $nama, $jenisKelamin, $lokasiAduan, $tanggalSimpan, $saran, $jawaban);
```

## Isi Notifikasi Telegram

Pesan yang dikirim ke channel berisi ringkasan survei baru, antara lain:

- Jenis survei
- Nama pelapor
- Jenis kelamin
- Lokasi aduan
- Waktu kirim
- Total jawaban
- Jumlah jawaban `Puas`
- Jumlah jawaban `Kurang Puas`
- Jumlah jawaban `Tidak Puas`
- Status ada atau tidaknya saran
- Isi saran, jika ada

## Form Yang Memicu Notifikasi Telegram

Notifikasi Telegram akan terkirim jika pasien submit dari salah satu file berikut:

- `survey/form_ralan.php`
- `survey/form_ranap.php`

Kedua form tersebut mengarah ke proses yang sama:

- `survey/simpan_jawaban.php`

## Cara Uji Notifikasi Telegram

1. Isi `TELEGRAM_BOT_TOKEN` di file `.env`.
2. Pastikan bot sudah menjadi admin di channel `@form_survey_rspi`.
3. Buka aplikasi:

```text
http://localhost/survey-kepuasan/
```

4. Pilih salah satu form survei:
   - `Rawat Jalan`
   - `Rawat Inap`
5. Isi form sampai selesai.
6. Klik kirim.
7. Cek channel Telegram, seharusnya notifikasi baru muncul.

## Jika Notifikasi Telegram Tidak Masuk

Periksa hal berikut:

1. Token bot di file `.env` sudah benar.
2. Username channel di `TELEGRAM_CHAT_ID` sudah benar.
3. Bot sudah ditambahkan sebagai admin channel.
4. Server bisa mengakses internet keluar ke `api.telegram.org`.
5. `curl` atau `file_get_contents()` bisa dipakai oleh PHP.
6. Data survei berhasil tersimpan terlebih dahulu.

Catatan:
- Pada implementasi ini, jika Telegram gagal, data survei tetap disimpan ke database.
- Jadi kegagalan Telegram tidak membatalkan data responden.

## File Yang Terkait Dengan Fitur Telegram

- `.env`
  Tempat konfigurasi token bot dan target channel Telegram.
- `config/app.php`
  Membaca nilai Telegram dari `.env` dan menyiapkan konstanta aplikasi.
- `survey/simpan_jawaban.php`
  Tempat pemanggilan notifikasi setelah survei berhasil tersimpan.
- `survey/form_ralan.php`
  Form survei rawat jalan.
- `survey/form_ranap.php`
  Form survei rawat inap.

## Catatan Keamanan

- Jangan upload token bot ke repository publik.
- Jangan bagikan screenshot token.
- Jika token pernah terlihat orang lain, segera `revoke` dari `@BotFather`.
- Lebih aman jika token nantinya dipindahkan ke file config lokal yang tidak ikut di-commit.

## Pengembangan Selanjutnya Yang Disarankan

- Simpan token Telegram di file `.env` atau config lokal terpisah
- Tambahkan log error untuk kegagalan kirim Telegram
- Tambahkan notifikasi berbeda untuk survei dengan nilai buruk
- Tambahkan notifikasi khusus jika komplain otomatis terbentuk

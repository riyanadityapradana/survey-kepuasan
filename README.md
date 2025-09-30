🔹🔹 ALUR SISTEM SURVEY KEPUASAN (PHP Native) 🔹🔹

1. Persiapan Database

Buat database baru, contoh: survey_kepuasan.

Import tabel-tabel berikut (SQL sudah aku sesuaikan):

CREATE DATABASE survey_kepuasan;
USE survey_kepuasan;

-- tabel admin user
CREATE TABLE users (
id_user INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) UNIQUE,
password VARCHAR(255),
role ENUM('admin') DEFAULT 'admin'
);

-- tabel survey
CREATE TABLE surveys (
id_survey INT AUTO_INCREMENT PRIMARY KEY,
judul_survey VARCHAR(255),
deskripsi TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- tabel pertanyaan
CREATE TABLE questions (
id_question INT AUTO_INCREMENT PRIMARY KEY,
id_survey INT,
pertanyaan TEXT,
FOREIGN KEY (id_survey) REFERENCES surveys(id_survey) ON DELETE CASCADE
);

-- tabel respon (untuk tracking setiap pengisian)
CREATE TABLE responses (
id_response INT AUTO_INCREMENT PRIMARY KEY,
id_survey INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (id_survey) REFERENCES surveys(id_survey) ON DELETE CASCADE
);

-- tabel jawaban
CREATE TABLE answers (
id_answer INT AUTO_INCREMENT PRIMARY KEY,
id_response INT,
id_question INT,
jawaban ENUM('senang','biasa','buruk'),
FOREIGN KEY (id_response) REFERENCES responses(id_response) ON DELETE CASCADE,
FOREIGN KEY (id_question) REFERENCES questions(id_question) ON DELETE CASCADE
);

2. Alur Admin

Admin punya akses ke halaman backend untuk mengelola survey.

🔹Login → Admin masuk dengan username & password.

🔹Kelola Survey

    - Buat survey baru (judul_survey, deskripsi).

    - Tambah pertanyaan (pertanyaan) yang ingin dinilai.

    - Contoh: "Bagaimana pelayanan pendaftaran?", "Bagaimana makanan pasien?", dll.

🔹Bagikan Survey

    - Admin bisa ambil link per pertanyaan → contoh:
    http://localhost/survey-kepuasan/question.php?id=5
    (5 = id_question dari tabel).

    - Link bisa ditempel ke QR Code lalu ditaruh di lokasi strategis (loket, ruang tunggu, kamar pasien).

🔹Lihat Hasil

    - Admin buka menu laporan.

    - Bisa lihat rekap per pertanyaan: berapa yang jawab senang, biasa, buruk.

    - Hasil ditampilkan dalam bentuk tabel + grafik (Chart.js).

    - Bisa export ke Excel/PDF.

3.  Alur Responden (Pasien/Pengunjung)

    Pasien scan QR Code / buka link yang diberikan.
    Contoh: http://localhost/survey-kepuasan/question.php?id=5.

    Halaman menampilkan pertanyaan dari database (questions).

    Pasien pilih salah satu emot:

        😊 Puas → jawaban = senang

        😐 Biasa saja → jawaban = biasa

        😞 Tidak puas → jawaban = buruk

    Pasien klik emot → jawaban langsung tersimpan ke database.

    Halaman menampilkan pesan “Terima kasih atas feedback Anda!”.

4.  Alur Data di Database

Saat pasien submit:

Sistem bikin entry baru di responses (satu pengisian survey).

Jawaban disimpan di answers dengan id_response, id_question, dan jawaban.

Contoh:

Pertanyaan: Bagaimana pelayanan pendaftaran?

Responden pilih: 😊 Senang

Data di answers:

id_response id_question jawaban
1 5 senang 5. Alur Laporan Admin

Admin pilih survey di menu laporan.

Sistem ambil semua pertanyaan di survey itu.

Untuk tiap pertanyaan, sistem hitung jumlah jawaban:

SELECT jawaban, COUNT(\*) as total
FROM answers
WHERE id_question = 5
GROUP BY jawaban;

Tampilkan hasil:

😊 Senang = 70

😐 Biasa saja = 20

😞 Buruk = 10

Visualisasi dengan Chart.js → pie chart/bar chart.

6. Struktur Folder Project
   survey-kepuasan/
   │
   ├── koneksi.php # koneksi database
   ├── index.php # halaman awal / dashboard
   ├── login.php # login admin
   ├── survey.php # tampil survey (banyak pertanyaan)
   ├── question.php # tampil 1 pertanyaan (dari id_question)
   ├── simpan_jawaban.php # proses simpan banyak jawaban
   ├── simpan_single.php # proses simpan 1 jawaban
   ├── laporan.php # laporan admin (tabel + grafik)
   ├── /admin/ # folder khusus admin
   └── /assets/ # CSS, JS, gambar

7. Flow Singkat (Diagram Teks)
   ADMIN:
   Login → Buat Survey → Tambah Pertanyaan → Bagikan Link/QR
   ↓
   RESPONDEN:
   Akses Link → Pilih Emot → Submit Jawaban → Data Masuk DB
   ↓
   ADMIN:
   Buka Laporan → Lihat Rekap (Tabel + Grafik) → Export PDF/Excel

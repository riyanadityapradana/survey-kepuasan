<?php
session_start();

require_once __DIR__ . '/env.php';
load_env_file(dirname(__DIR__) . '/.env');
require_once __DIR__ . '/app.php';
date_default_timezone_set(APP_TIMEZONE);

$host = env('DB_HOST', 'localhost');
$user = env('DB_USERNAME', 'root');
$pass = env('DB_PASSWORD', '');
$db   = env('DB_DATABASE', 'survei_rs');

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

require_once dirname(__DIR__) . '/library/src/Exception.php';
require_once dirname(__DIR__) . '/library/src/PHPMailer.php';
require_once dirname(__DIR__) . '/library/src/SMTP.php';

function url(string $path = ''): string
{
    return BASE_URL . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect_ke(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): string
{
    if (!isset($_SESSION['flash'][$key])) {
        return '';
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function set_old_input(array $input): void
{
    $_SESSION['old_input'] = $input;
}

function pull_old_input(): array
{
    $input = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    return is_array($input) ? $input : [];
}

function clear_old_input(): void
{
    unset($_SESSION['old_input']);
}

function old_input(array $input, string $key, string $default = ''): string
{
    $value = $input[$key] ?? $default;

    return is_scalar($value) ? trim((string) $value) : $default;
}

function cek_login(): void
{
    if (empty($_SESSION['user'])) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        redirect_ke('auth/login.php');
    }
}

function user_saat_ini(): ?array
{
    return $_SESSION['user'] ?? null;
}

function user_punya_role(string ...$roles): bool
{
    $user = user_saat_ini();
    if (!$user || empty($user['role'])) {
        return false;
    }

    return in_array($user['role'], $roles, true);
}

function cek_role(array $roles): void
{
    cek_login();

    if (!user_punya_role(...$roles)) {
        set_flash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect_ke('admin/dashboard.php');
    }
}

function daftar_lokasi_aduan(): array
{
    return [
        'Lantai 1 (Rawat Jalan)',
        'Lantai 1 (IGD)',
        'Lantai 2 (Rawat Inap)',
        'Lantai 3 (Rawat Inap)',
    ];
}

function query_string(array $overrides = [], array $exclude = []): string
{
    $params = $_GET;

    foreach ($exclude as $key) {
        unset($params[$key]);
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
            continue;
        }

        $params[$key] = $value;
    }

    $query = http_build_query($params);

    return $query !== '' ? '?' . $query : '';
}

function render_pagination(int $currentPage, int $totalPages): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-end flex-wrap mb-0">';

    $prevClass = $currentPage <= 1 ? ' disabled' : '';
    $html .= '<li class="page-item' . $prevClass . '"><a class="page-link" href="' . e(query_string(['page' => max(1, $currentPage - 1)])) . '">Sebelumnya</a></li>';

    for ($page = $start; $page <= $end; $page++) {
        $activeClass = $page === $currentPage ? ' active' : '';
        $html .= '<li class="page-item' . $activeClass . '"><a class="page-link" href="' . e(query_string(['page' => $page])) . '">' . $page . '</a></li>';
    }

    $nextClass = $currentPage >= $totalPages ? ' disabled' : '';
    $html .= '<li class="page-item' . $nextClass . '"><a class="page-link" href="' . e(query_string(['page' => min($totalPages, $currentPage + 1)])) . '">Berikutnya</a></li>';
    $html .= '</ul></nav>';

    return $html;
}

function log_audit(mysqli $conn, string $aksi, string $entitas, ?int $entitasId = null, string $deskripsi = ''): void
{
    $user = user_saat_ini();
    if (!$user) {
        return;
    }

    $userId = (int) $user['id'];
    $stmt = mysqli_prepare($conn, 'INSERT INTO audit_logs (user_id, aksi, entitas, entitas_id, deskripsi, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    mysqli_stmt_bind_param($stmt, 'issis', $userId, $aksi, $entitas, $entitasId, $deskripsi);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function hitung_ringkasan(mysqli $conn): array
{
    $data = [
        'pertanyaan' => 0,
        'responden' => 0,
        'jawaban' => 0,
        'ralan' => 0,
        'ranap' => 0,
    ];

    $queries = [
        'pertanyaan' => "SELECT COUNT(*) AS total FROM pertanyaan",
        'responden' => "SELECT COUNT(*) AS total FROM responden",
        'jawaban' => "SELECT COUNT(*) AS total FROM jawaban",
        'ralan' => "SELECT COUNT(*) AS total FROM responden WHERE jenis = 'ralan'",
        'ranap' => "SELECT COUNT(*) AS total FROM responden WHERE jenis = 'ranap'",
    ];

    foreach ($queries as $key => $sql) {
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $data[$key] = (int) ($row['total'] ?? 0);
        }
    }

    return $data;
}

function ambil_pertanyaan_berdasarkan_jenis(mysqli $conn, string $jenis): array
{
    $stmt = mysqli_prepare($conn, 'SELECT id, kategori, pertanyaan FROM pertanyaan WHERE jenis = ? ORDER BY kategori ASC, id ASC');
    mysqli_stmt_bind_param($stmt, 's', $jenis);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $kelompok = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $kelompok[$row['kategori']][] = $row;
    }

    mysqli_stmt_close($stmt);

    return $kelompok;
}

function kategori_waktu_tanggap(?string $tanggalKomplain, ?string $tanggalTindakLanjut): array
{
    if (empty($tanggalKomplain) || empty($tanggalTindakLanjut)) {
        return ['kode' => 'belum', 'label' => '-', 'selisih_jam' => null];
    }

    try {
        $mulai = new DateTime($tanggalKomplain);
        $selesai = new DateTime($tanggalTindakLanjut);
    } catch (Throwable $e) {
        return ['kode' => 'belum', 'label' => '-', 'selisih_jam' => null];
    }

    $selisihJam = ($selesai->getTimestamp() - $mulai->getTimestamp()) / 3600;
    if ($selisihJam <= 24) {
        return ['kode' => 'hijau', 'label' => 'Hijau', 'selisih_jam' => round($selisihJam, 2)];
    }
    if ($selisihJam <= 72) {
        return ['kode' => 'kuning', 'label' => 'Kuning', 'selisih_jam' => round($selisihJam, 2)];
    }

    return ['kode' => 'merah', 'label' => 'Merah', 'selisih_jam' => round($selisihJam, 2)];
}

function badge_kategori_tanggap(array $kategori): string
{
    $class = 'text-bg-secondary';
    if (($kategori['kode'] ?? '') === 'hijau') {
        $class = 'text-bg-success';
    } elseif (($kategori['kode'] ?? '') === 'kuning') {
        $class = 'text-bg-warning text-dark';
    } elseif (($kategori['kode'] ?? '') === 'merah') {
        $class = 'text-bg-danger';
    }

    return '<span class="badge ' . $class . '">' . e((string) ($kategori['label'] ?? '-')) . '</span>';
}

function daftar_status_komplain(): array
{
    return [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
    ];
}

function badge_nilai(int $nilai): string
{
    if ($nilai === 3) {
        return '<span class="badge text-bg-success">Puas</span>';
    }

    if ($nilai === 2) {
        return '<span class="badge text-bg-warning text-dark">Kurang Puas</span>';
    }

    return '<span class="badge text-bg-danger">Tidak Puas</span>';
}

function komplain_by_responden(mysqli $conn, int $idResponden): ?array
{
    $stmt = mysqli_prepare($conn, 'SELECT id, status, sumber_data FROM komplain WHERE id_responden = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $idResponden);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result) ?: null;
    mysqli_stmt_close($stmt);

    return $row;
}

function perlu_sinkron_komplain(string $saran, array $jawaban): bool
{
    if (trim($saran) !== '') {
        return true;
    }

    foreach ($jawaban as $nilai) {
        if ((int) $nilai === 1) {
            return true;
        }
    }

    return false;
}

function ringkas_pertanyaan_komplain(array $pertanyaan): string
{
    if (!$pertanyaan) {
        return '';
    }

    $pertanyaan = array_values(array_unique(array_filter(array_map('trim', $pertanyaan))));
    if (!$pertanyaan) {
        return '';
    }

    $potongan = array_slice($pertanyaan, 0, 3);
    $teks = implode(' | ', $potongan);

    if (count($pertanyaan) > 3) {
        $teks .= ' | +' . (count($pertanyaan) - 3) . ' pertanyaan lainnya';
    }

    return $teks;
}

function susun_aduan_komplain_otomatis(array $pertanyaanByJenis, array $jawaban, string $saran): string
{
    $mapPertanyaan = [];
    foreach ($pertanyaanByJenis as $items) {
        foreach ($items as $item) {
            $mapPertanyaan[(int) $item['id']] = $item['pertanyaan'];
        }
    }

    $tidakPuas = [];
    foreach ($jawaban as $idPertanyaan => $nilai) {
        if ((int) $nilai === 1 && isset($mapPertanyaan[(int) $idPertanyaan])) {
            $tidakPuas[] = $mapPertanyaan[(int) $idPertanyaan];
        }
    }

    $bagian = [];
    $saran = trim($saran);
    if ($saran !== '') {
        $bagian[] = 'Saran / keluhan responden: ' . $saran;
    }

    $ringkasanTidakPuas = ringkas_pertanyaan_komplain($tidakPuas);
    if ($ringkasanTidakPuas !== '') {
        $bagian[] = 'Jawaban tidak puas terdeteksi pada: ' . $ringkasanTidakPuas;
    }

    if (!$bagian) {
        $bagian[] = 'Komplain otomatis dari survei kepuasan pasien.';
    }

    return implode("\n\n", $bagian);
}

function sinkron_komplain_otomatis(mysqli $conn, int $idResponden, string $nama, string $lokasiAduan, string $tanggalKomplain, string $saran, array $jawaban, array $pertanyaanByJenis): ?int
{
    if (!perlu_sinkron_komplain($saran, $jawaban)) {
        return null;
    }

    $existing = komplain_by_responden($conn, $idResponden);
    if ($existing) {
        return (int) $existing['id'];
    }

    $aduan = susun_aduan_komplain_otomatis($pertanyaanByJenis, $jawaban, $saran);
    $status = 'baru';
    $sumberData = 'survey_otomatis';
    $createdBy = null;
    $keterangan = null;
    $tanggalTindak = null;

    $stmt = mysqli_prepare($conn, 'INSERT INTO komplain (id_responden, area_komplain, identitas_pasien, aduan, tanggal_komplain, tanggal_tindak_lanjut, status, keterangan_tindak_lanjut, sumber_data, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'issssssssi', $idResponden, $lokasiAduan, $nama, $aduan, $tanggalKomplain, $tanggalTindak, $status, $keterangan, $sumberData, $createdBy);
    mysqli_stmt_execute($stmt);
    $komplainId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    return $komplainId > 0 ? $komplainId : null;
}

function label_jenis_survei(string $jenis): string
{
    return $jenis === 'ranap' ? 'Rawat Inap' : 'Rawat Jalan';
}

function ringkasan_nilai_survei(array $jawaban): array
{
    $ringkasan = [
        'puas' => 0,
        'kurang_puas' => 0,
        'tidak_puas' => 0,
    ];

    foreach ($jawaban as $nilai) {
        $nilai = (int) $nilai;
        if ($nilai === 3) {
            $ringkasan['puas']++;
            continue;
        }
        if ($nilai === 2) {
            $ringkasan['kurang_puas']++;
            continue;
        }
        if ($nilai === 1) {
            $ringkasan['tidak_puas']++;
        }
    }

    return $ringkasan;
}

function kirim_pesan_telegram(string $pesan): bool
{
    if (!TELEGRAM_NOTIF_ENABLED) {
        return false;
    }

    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';
    $payload = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $pesan,
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($ch);
        curl_close($ch);

        return $curlError === 0 && $httpCode >= 200 && $httpCode < 300 && $response !== false;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded
",
            'content' => http_build_query($payload),
            'timeout' => 10,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);

    return $response !== false;
}

function kirim_notifikasi_telegram_survei(string $jenis, string $nama, string $jenisKelamin, string $lokasiAduan, string $tanggalSimpan, string $saran, array $jawaban): bool
{
    $jenisLabel = label_jenis_survei($jenis);
    $ringkasan = ringkasan_nilai_survei($jawaban);
    $saran = trim($saran);
    $statusSaran = $saran !== '' ? 'Ada' : 'Tidak ada';

    $pesan = implode(PHP_EOL, [
        'Survei Kepuasan Baru',
        'Jenis: ' . $jenisLabel,
        'Nama Pelapor: ' . $nama,
        'Jenis Kelamin: ' . $jenisKelamin,
        'Lokasi Aduan: ' . $lokasiAduan,
        'Waktu Kirim: ' . $tanggalSimpan,
        'Total Jawaban: ' . count($jawaban),
        'Puas: ' . $ringkasan['puas'],
        'Kurang Puas: ' . $ringkasan['kurang_puas'],
        'Tidak Puas: ' . $ringkasan['tidak_puas'],
        'Saran: ' . $statusSaran,
    ]);

    if ($saran !== '') {
        $pesan .= PHP_EOL . 'Isi Saran: ' . mb_substr($saran, 0, 300);
    }

    return kirim_pesan_telegram($pesan);
}

function kirim_email(string $subject, string $htmlBody, ?string $plainBody = null): bool
{
    if (!MAIL_NOTIF_ENABLED) {
        return false;
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress(MAIL_TO_ADDRESS, MAIL_TO_NAME);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $plainBody ?? trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], PHP_EOL, $htmlBody)));

        return $mail->send();
    } catch (\Throwable $e) {
        return false;
    }
}

function kirim_notifikasi_email_survei(string $jenis, string $nama, string $jenisKelamin, string $tanggungan, string $lokasiAduan, string $tanggalSimpan, string $saran, array $jawaban): bool
{
    $jenisLabel = label_jenis_survei($jenis);
    $ringkasan = ringkasan_nilai_survei($jawaban);
    $saran = trim($saran);

    $subject = 'Survei Kepuasan Baru - ' . $jenisLabel;
    $htmlBody = '
        <div style="font-family:Segoe UI,Tahoma,sans-serif;color:#1f3550;line-height:1.6">
            <h2 style="margin-bottom:8px;color:#0d6efd;">Survei Kepuasan Baru</h2>
            <p style="margin-top:0;">Ada survei baru yang baru saja masuk ke sistem.</p>
            <table cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:720px;">
                <tr><td style="border:1px solid #d8e6ff;"><strong>Jenis Survei</strong></td><td style="border:1px solid #d8e6ff;">' . e($jenisLabel) . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Nama Pelapor</strong></td><td style="border:1px solid #d8e6ff;">' . e($nama) . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Jenis Kelamin</strong></td><td style="border:1px solid #d8e6ff;">' . e($jenisKelamin) . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Tanggungan</strong></td><td style="border:1px solid #d8e6ff;">' . e($tanggungan) . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Lokasi Aduan</strong></td><td style="border:1px solid #d8e6ff;">' . e($lokasiAduan) . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Waktu Kirim</strong></td><td style="border:1px solid #d8e6ff;">' . e($tanggalSimpan) . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Total Jawaban</strong></td><td style="border:1px solid #d8e6ff;">' . count($jawaban) . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Puas</strong></td><td style="border:1px solid #d8e6ff;">' . $ringkasan['puas'] . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Kurang Puas</strong></td><td style="border:1px solid #d8e6ff;">' . $ringkasan['kurang_puas'] . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Tidak Puas</strong></td><td style="border:1px solid #d8e6ff;">' . $ringkasan['tidak_puas'] . '</td></tr>
                <tr><td style="border:1px solid #d8e6ff;"><strong>Saran</strong></td><td style="border:1px solid #d8e6ff;">' . ($saran !== '' ? nl2br(e($saran)) : '-') . '</td></tr>
            </table>
            <p style="margin-top:16px;font-size:12px;color:#60758f;">Email ini dikirim otomatis oleh sistem survei.</p>
        </div>';

    $plainBody = implode(PHP_EOL, [
        'Survei Kepuasan Baru',
        'Jenis Survei: ' . $jenisLabel,
        'Nama Pelapor: ' . $nama,
        'Jenis Kelamin: ' . $jenisKelamin,
        'Tanggungan: ' . $tanggungan,
        'Lokasi Aduan: ' . $lokasiAduan,
        'Waktu Kirim: ' . $tanggalSimpan,
        'Total Jawaban: ' . count($jawaban),
        'Puas: ' . $ringkasan['puas'],
        'Kurang Puas: ' . $ringkasan['kurang_puas'],
        'Tidak Puas: ' . $ringkasan['tidak_puas'],
        'Saran: ' . ($saran !== '' ? $saran : '-'),
    ]);

    return kirim_email($subject, $htmlBody, $plainBody);
}
?>

<?php
session_start();

define('APP_NAME', 'Survei Kepuasan RSPI');
define('BASE_URL', '/survey-kepuasan');

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'survei_rs';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

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
?>

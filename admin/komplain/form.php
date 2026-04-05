<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

function nilai_form_komplain(array $data, string $key, string $default = ''): string
{
    return trim((string) ($data[$key] ?? $default));
}

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$idResponden = (int) ($_GET['id_responden'] ?? $_POST['id_responden'] ?? 0);
$isEdit = basename($_SERVER['PHP_SELF']) === 'edit.php';
$returnUrl = trim($_GET['return'] ?? $_POST['return'] ?? url('admin/komplain/index.php'));
$responden = null;
$formData = [
    'area_komplain' => '',
    'identitas_pasien' => '',
    'aduan' => '',
    'tanggal_komplain' => date('Y-m-d\TH:i'),
    'tanggal_tindak_lanjut' => '',
    'status' => 'baru',
    'keterangan_tindak_lanjut' => '',
];

if ($isEdit) {
    if ($id <= 0) {
        set_flash('error', 'ID komplain tidak valid.');
        redirect_ke('admin/komplain/index.php');
    }
    $stmt = mysqli_prepare($conn, 'SELECT * FROM komplain WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$existing) {
        set_flash('error', 'Data komplain tidak ditemukan.');
        redirect_ke('admin/komplain/index.php');
    }
    $idResponden = (int) ($existing['id_responden'] ?? 0);
    $formData = [
        'area_komplain' => $existing['area_komplain'],
        'identitas_pasien' => $existing['identitas_pasien'],
        'aduan' => $existing['aduan'],
        'tanggal_komplain' => !empty($existing['tanggal_komplain']) ? date('Y-m-d\TH:i', strtotime($existing['tanggal_komplain'])) : '',
        'tanggal_tindak_lanjut' => !empty($existing['tanggal_tindak_lanjut']) ? date('Y-m-d\TH:i', strtotime($existing['tanggal_tindak_lanjut'])) : '',
        'status' => $existing['status'],
        'keterangan_tindak_lanjut' => $existing['keterangan_tindak_lanjut'] ?? '',
    ];
} elseif ($idResponden > 0) {
    $existingKomplain = komplain_by_responden($conn, $idResponden);
    if ($existingKomplain) {
        set_flash('success', 'Responden ini sudah memiliki data komplain.');
        redirect_ke('admin/komplain/edit.php?id=' . (int) $existingKomplain['id']);
    }

    $stmt = mysqli_prepare($conn, 'SELECT id, nama, tanggungan, lokasi_aduan, saran, tanggal FROM responden WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $idResponden);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $responden = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($responden) {
        $aduanDefault = trim((string) ($responden['saran'] ?? ''));
        if ($aduanDefault === '') {
            $aduanDefault = 'Komplain lanjutan dari hasil survei kepuasan pasien.';
        }

        $formData['area_komplain'] = $responden['lokasi_aduan'];
        $formData['identitas_pasien'] = $responden['nama'];
        $formData['aduan'] = $aduanDefault;
        $formData['tanggal_komplain'] = !empty($responden['tanggal']) ? date('Y-m-d\TH:i', strtotime($responden['tanggal'])) : $formData['tanggal_komplain'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'area_komplain' => nilai_form_komplain($_POST, 'area_komplain'),
        'identitas_pasien' => nilai_form_komplain($_POST, 'identitas_pasien'),
        'aduan' => nilai_form_komplain($_POST, 'aduan'),
        'tanggal_komplain' => nilai_form_komplain($_POST, 'tanggal_komplain'),
        'tanggal_tindak_lanjut' => nilai_form_komplain($_POST, 'tanggal_tindak_lanjut'),
        'status' => nilai_form_komplain($_POST, 'status', 'baru'),
        'keterangan_tindak_lanjut' => nilai_form_komplain($_POST, 'keterangan_tindak_lanjut'),
    ];

    if ($formData['area_komplain'] === '' || $formData['identitas_pasien'] === '' || $formData['aduan'] === '' || $formData['tanggal_komplain'] === '') {
        set_flash('error', 'Area, identitas pasien, aduan, dan tanggal komplain wajib diisi.');
        redirect_ke($isEdit ? 'admin/komplain/edit.php?id=' . $id : 'admin/komplain/tambah.php' . ($idResponden > 0 ? '?id_responden=' . $idResponden : ''));
    }

    if (!array_key_exists($formData['status'], daftar_status_komplain())) {
        $formData['status'] = 'baru';
    }

    $tanggalKomplain = str_replace('T', ' ', $formData['tanggal_komplain']) . ':00';
    $tanggalTindak = $formData['tanggal_tindak_lanjut'] !== '' ? str_replace('T', ' ', $formData['tanggal_tindak_lanjut']) . ':00' : null;
    $userId = (int) user_saat_ini()['id'];
    $sumberData = $idResponden > 0 ? 'survey_otomatis' : 'manual';

    if (!$isEdit && $idResponden > 0 && komplain_by_responden($conn, $idResponden)) {
        set_flash('error', 'Responden ini sudah memiliki data komplain.');
        redirect_ke('admin/komplain/index.php');
    }

    if ($isEdit) {
        $stmt = mysqli_prepare($conn, 'UPDATE komplain SET area_komplain = ?, identitas_pasien = ?, aduan = ?, tanggal_komplain = ?, tanggal_tindak_lanjut = ?, status = ?, keterangan_tindak_lanjut = ?, updated_by = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'sssssssii', $formData['area_komplain'], $formData['identitas_pasien'], $formData['aduan'], $tanggalKomplain, $tanggalTindak, $formData['status'], $formData['keterangan_tindak_lanjut'], $userId, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        log_audit($conn, 'ubah', 'komplain', $id, 'Memperbarui data komplain area ' . $formData['area_komplain'] . '.');
        set_flash('success', 'Data komplain berhasil diperbarui.');
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO komplain (id_responden, area_komplain, identitas_pasien, aduan, tanggal_komplain, tanggal_tindak_lanjut, status, keterangan_tindak_lanjut, sumber_data, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'issssssssi', $idResponden, $formData['area_komplain'], $formData['identitas_pasien'], $formData['aduan'], $tanggalKomplain, $tanggalTindak, $formData['status'], $formData['keterangan_tindak_lanjut'], $sumberData, $userId);
        mysqli_stmt_execute($stmt);
        $komplainId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        log_audit($conn, 'tambah', 'komplain', $komplainId, 'Menambahkan komplain area ' . $formData['area_komplain'] . '.');
        set_flash('success', 'Data komplain berhasil ditambahkan.');
    }

    if ($returnUrl === '' || str_contains($returnUrl, 'javascript:')) {
        $returnUrl = url('admin/komplain/index.php');
    }
    header('Location: ' . $returnUrl);
    exit;
}

$pageTitle = $isEdit ? 'Edit Komplain' : 'Tambah Komplain';
$activeMenu = 'komplain';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/admin_nav.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card google-card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold mb-1"><?= e($pageTitle); ?></h3>
                            <p class="text-muted mb-0">Input komplain dan tindak lanjut untuk laporan kecepatan waktu tanggap.</p>
                        </div>
                        <a href="<?= e($returnUrl); ?>" class="btn btn-outline-primary">Kembali</a>
                    </div>
                    <form method="post">
                        <?php if ($isEdit) : ?><input type="hidden" name="id" value="<?= $id; ?>"><?php endif; ?>
                        <input type="hidden" name="id_responden" value="<?= $idResponden; ?>">
                        <input type="hidden" name="return" value="<?= e($returnUrl); ?>">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Area Komplain</label><input type="text" name="area_komplain" class="form-control" value="<?= e($formData['area_komplain']); ?>" required></div>
                            <div class="col-md-4"><label class="form-label">Identitas Pasien / Pelapor</label><input type="text" name="identitas_pasien" class="form-control" value="<?= e($formData['identitas_pasien']); ?>" required></div>
                            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (daftar_status_komplain() as $kode => $label) : ?><option value="<?= e($kode); ?>" <?= $formData['status'] === $kode ? 'selected' : ''; ?>><?= e($label); ?></option><?php endforeach; ?></select></div>
                            <?php if (!$isEdit && !empty($responden['tanggungan'])) : ?><div class="col-md-4"><label class="form-label">Tanggungan Responden</label><input type="text" class="form-control" value="<?= e($responden['tanggungan']); ?>" readonly></div><?php endif; ?>
                            <div class="col-md-6"><label class="form-label">Tanggal / Jam Komplain</label><input type="datetime-local" name="tanggal_komplain" class="form-control" value="<?= e($formData['tanggal_komplain']); ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Tanggal / Jam Tindak Lanjut</label><input type="datetime-local" name="tanggal_tindak_lanjut" class="form-control" value="<?= e($formData['tanggal_tindak_lanjut']); ?>"></div>
                            <div class="col-12"><label class="form-label">Aduan Keluhan / Komplain</label><textarea name="aduan" class="form-control" rows="4" required><?= e($formData['aduan']); ?></textarea></div>
                            <div class="col-12"><label class="form-label">Keterangan Tindak Lanjut</label><textarea name="keterangan_tindak_lanjut" class="form-control" rows="4"><?= e($formData['keterangan_tindak_lanjut']); ?></textarea></div>
                        </div>
                        <button type="submit" class="btn btn-rs-primary mt-4"><i class="fa-solid fa-floppy-disk me-2"></i><?= $isEdit ? 'Update' : 'Simpan'; ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

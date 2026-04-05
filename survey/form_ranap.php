<?php
require_once __DIR__ . '/../config/koneksi.php';

$jenis = 'ranap';
$judul = 'Survei Kepuasan Rawat Inap';
$subjudul = 'Masukan dari pasien rawat inap sangat membantu rumah sakit meningkatkan pelayanan.';
$kelompokPertanyaan = ambil_pertanyaan_berdasarkan_jenis($conn, $jenis);
$error = get_flash('error');
$oldInput = pull_old_input();
$pageTitle = $judul;
$activePublic = 'ranap';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/public_nav.php';
$inlineScripts = ["const form = document.querySelector('[data-survey-form]'); if (form) { let dirty = false; form.addEventListener('input', () => { dirty = true; }); form.addEventListener('change', () => { dirty = true; }); window.addEventListener('beforeunload', (event) => { if (!dirty) { return; } event.preventDefault(); event.returnValue = ''; }); form.addEventListener('submit', () => { dirty = false; }); }"];
?>
<div class="container py-4 py-lg-5">
    <div class="form-header-card mb-4">
        <div class="form-header-bar"></div>
        <div class="form-header-banner">
            <img src="<?= url('assets/img/Header Google Form.png'); ?>" alt="Header Form Survei" class="form-header-image">
        </div>
        <div class="p-4 p-lg-5">
            <span class="pill-label mb-3 d-inline-flex"><i class="fa-solid fa-bed-pulse me-2"></i>Form Pasien Rawat Inap</span>
            <h1 class="fw-bold mb-2"><?= e($judul); ?></h1>
            <p class="text-muted mb-2"><?= e($subjudul); ?></p>
            <p class="form-hint mb-0">Jika Anda menutup halaman sebelum mengirim, browser akan memberi peringatan agar jawaban tidak hilang.</p>
        </div>
    </div>

    <?php if ($error) : ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
    <div class="survey-meta-grid mb-4">
        <div class="survey-meta-card"><strong>Durasi singkat</strong><span>Form tetap ringan saat dibuka dari ponsel atau komputer.</span></div>
        <div class="survey-meta-card"><strong>Jawaban sederhana</strong><span>Anda hanya perlu memilih satu jawaban untuk tiap pertanyaan.</span></div>
        <div class="survey-meta-card"><strong>Lokasi fleksibel</strong><span>Nama lantai, ruangan, atau poli bisa ditulis sesuai kondisi layanan.</span></div>
    </div>

    <?php if (empty($kelompokPertanyaan)) : ?>
        <div class="alert alert-warning">Belum ada pertanyaan untuk survei rawat inap.</div>
    <?php else : ?>
        <form action="<?= url('survey/simpan_jawaban.php'); ?>" method="post" data-survey-form>
            <input type="hidden" name="jenis" value="ranap">
            <div class="card google-card mb-4 survey-form-shell">
                <div class="card-body p-4">
                    <div class="section-heading mb-4">
                        <h5 class="fw-semibold mb-1">Data Responden</h5>
                        <p class="text-muted mb-0">Lengkapi identitas pelapor agar masukan lebih mudah dipahami tim admin.</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nama Keluarga Pasien/Pelapor</label><input type="text" name="nama" class="form-control input-soft" required maxlength="100" value="<?= e(old_input($oldInput, 'nama')); ?>"></div>
                        <div class="col-md-6"><label class="form-label">Jenis Kelamin</label><select name="jenis_kelamin" class="form-select input-soft" required><option value="">Pilih Jenis Kelamin</option><option value="Laki-Laki" <?= old_input($oldInput, 'jenis_kelamin') === 'Laki-Laki' ? 'selected' : ''; ?>>Laki-Laki</option><option value="Perempuan" <?= old_input($oldInput, 'jenis_kelamin') === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option></select></div>
                        <div class="col-md-6"><label class="form-label">Tanggungan Pasien</label><input type="text" name="tanggungan" class="form-control input-soft" required maxlength="50" placeholder="Contoh: BPJS, Umum, Asuransi" value="<?= e(old_input($oldInput, 'tanggungan')); ?>"></div>
                        <div class="col-md-6"><label class="form-label">Lokasi Aduan / Layanan</label><input type="text" name="lokasi_aduan" class="form-control input-soft" required maxlength="250" placeholder='Contoh "Lantai 3 atau Lantai 1 atau Poli Kandungan"' value="<?= e(old_input($oldInput, 'lokasi_aduan')); ?>"></div>
                    </div>
                </div>
            </div>
            <?php foreach ($kelompokPertanyaan as $kategori => $items) : ?>
                <div class="card google-card mb-4"><div class="card-body p-4"><div class="section-heading mb-4"><h4 class="fw-semibold mb-1"><?= e($kategori); ?></h4><p class="text-muted mb-0">Pilih satu jawaban untuk setiap pertanyaan.</p></div><?php foreach ($items as $index => $item) : ?><div class="question-block <?= $index < count($items) - 1 ? 'border-bottom pb-4 mb-4' : ''; ?>"><label class="form-label fw-medium d-block mb-3"><?= e($item['pertanyaan']); ?> <span class="text-danger">*</span></label><div class="row g-3"><div class="col-md-4"><label class="answer-card"><input type="radio" name="jawaban[<?= $item['id']; ?>]" value="3" required><span class="answer-content"><i class="fa-solid fa-face-smile text-success"></i><strong>Puas</strong><small>Nilai 3</small></span></label></div><div class="col-md-4"><label class="answer-card"><input type="radio" name="jawaban[<?= $item['id']; ?>]" value="2" required><span class="answer-content"><i class="fa-solid fa-face-meh text-warning"></i><strong>Kurang Puas</strong><small>Nilai 2</small></span></label></div><div class="col-md-4"><label class="answer-card"><input type="radio" name="jawaban[<?= $item['id']; ?>]" value="1" required><span class="answer-content"><i class="fa-solid fa-face-frown text-danger"></i><strong>Tidak Puas</strong><small>Nilai 1</small></span></label></div></div></div><?php endforeach; ?></div></div>
            <?php endforeach; ?>
            <div class="card google-card mb-4">
                <div class="card-body p-4">
                    <div class="section-heading mb-4">
                        <h4 class="fw-semibold mb-1">Saran dan Masukan</h4>
                        <p class="text-muted mb-0">Kolom ini opsional. Silakan isi jika ada saran tambahan untuk pelayanan kami.</p>
                    </div>
                    <label class="form-label fw-medium d-block mb-3">Tulis saran Anda dalam kolom di bawah ini</label>
                    <textarea name="saran" class="form-control form-textarea-large input-soft" rows="5" maxlength="1000" placeholder="Tuliskan saran, kritik, atau masukan tambahan di sini..."><?= e(old_input($oldInput, 'saran')); ?></textarea>
                </div>
            </div>
            <div class="text-end"><button type="submit" class="btn btn-rs-primary btn-lg"><i class="fa-solid fa-paper-plane me-2"></i>Kirim Jawaban</button></div>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>



<?php
require_once __DIR__ . '/../config/koneksi.php';

$jenis = trim($_GET['jenis'] ?? '');
if (!in_array($jenis, ['ralan', 'ranap'], true)) {
    redirect_ke('');
}

$isRalan = $jenis === 'ralan';
$pageTitle = 'Terima Kasih';
$activePublic = $isRalan ? 'ralan' : 'ranap';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/public_nav.php';
?>
<div class="container thankyou-wrap py-5">
    <div class="row justify-content-center w-100">
        <div class="col-lg-7">
            <div class="card google-card border-0 shadow-sm text-center">
                <div class="card-body p-4 p-lg-5">
                    <div class="thankyou-icon mx-auto mb-4"><i class="fa-solid fa-circle-check"></i></div>
                    <span class="pill-label mb-3 d-inline-flex">Jawaban Berhasil Dikirim</span>
                    <h1 class="fw-bold mb-3">Terima kasih atas partisipasi Anda.</h1>
                    <p class="text-muted mb-4">Masukan Anda untuk layanan <?= $isRalan ? 'rawat jalan' : 'rawat inap'; ?> sudah kami terima dan akan digunakan sebagai bahan evaluasi peningkatan mutu pelayanan.</p>
                    <div class="d-flex justify-content-center flex-wrap gap-3">
                        <a href="<?= $isRalan ? url('survey/form_ralan.php') : url('survey/form_ranap.php'); ?>" class="btn btn-outline-primary">Isi Survei Lagi</a>
                        <a href="<?= url(); ?>" class="btn btn-rs-primary">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

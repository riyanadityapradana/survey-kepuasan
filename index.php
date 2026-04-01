<?php
require_once __DIR__ . '/config/koneksi.php';

$stats = hitung_ringkasan($conn);
$pageTitle = APP_NAME;
$activePublic = '';
$stickyNav = true;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/public_nav.php';
?>
<main>
    <section class="hero-home py-5 py-lg-6">
        <div class="container">
            <div class="row g-4 g-lg-5 align-items-center">
                <div class="col-lg-7">
                    <div class="hero-copy pe-lg-4">
                        <span class="pill-label mb-3 d-inline-flex">Sistem Survei Kepuasan Pasien Rumah Sakit</span>
                        <h1 class="hero-title mb-3">Aplikasi survei pasien yang rapi, responsif, dan mudah dikelola admin.</h1>
                        <p class="hero-text mb-4">Pasien dapat mengisi survei rawat jalan maupun rawat inap dengan tampilan yang sederhana, sementara admin bisa memantau hasil, filter laporan, dan tren layanan langsung dari dashboard.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="<?= url('survey/form_ralan.php'); ?>" class="btn btn-rs-primary btn-lg px-4"><i class="fa-solid fa-stethoscope me-2"></i>Isi Survei Rawat Jalan</a>
                            <a href="<?= url('survey/form_ranap.php'); ?>" class="btn btn-outline-primary btn-lg px-4"><i class="fa-solid fa-bed-pulse me-2"></i>Isi Survei Rawat Inap</a>
                        </div>
                        <div class="hero-badges mt-4">
                            <span><i class="fa-solid fa-circle-check me-2"></i>Filter Laporan</span>
                            <span><i class="fa-solid fa-circle-check me-2"></i>Dashboard Grafik</span>
                            <span><i class="fa-solid fa-circle-check me-2"></i>Mobile Friendly</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-panel google-card border-0 h-100 patient-panel">
                        <div class="card-body p-4 p-lg-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="fw-semibold mb-1">Panduan Singkat Pasien</h5>
                                    <p class="text-muted small mb-0">Isi survei dengan cepat dan mudah.</p>
                                </div>
                                <div class="panel-icon"><i class="fa-solid fa-notes-medical"></i></div>
                            </div>
                            <div class="patient-steps mb-4">
                                <div class="patient-step"><span>1</span><div><strong>Pilih jenis layanan</strong><small>Tentukan rawat jalan atau rawat inap.</small></div></div>
                                <div class="patient-step"><span>2</span><div><strong>Jawab semua pertanyaan</strong><small>Pilih jawaban yang paling sesuai dengan pengalaman Anda.</small></div></div>
                                <div class="patient-step"><span>3</span><div><strong>Tulis saran bila perlu</strong><small>Tambahkan masukan di bagian akhir formulir.</small></div></div>
                            </div>
                            <div class="info-card mb-3">
                                <h6 class="fw-semibold mb-2">Yang Perlu Diketahui Pasien</h6>
                                <p class="mb-0 text-muted">Pengisian survei hanya memerlukan beberapa menit dan dirancang tetap nyaman saat dibuka dari handphone.</p>
                            </div>
                            <div class="info-card info-card-soft">
                                <h6 class="fw-semibold mb-2">Mengapa Survei Ini Penting?</h6>
                                <p class="mb-0 text-muted">Masukan Anda membantu rumah sakit mengevaluasi pelayanan dokter, perawat, fasilitas, dan proses layanan agar menjadi lebih baik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="pb-5">
        <div class="container">
            <div class="section-head text-center mb-4">
                <span class="section-kicker">Fitur Utama</span>
                <h2 class="section-title">Dirancang nyaman untuk pasien dan admin</h2>
                <p class="section-text">Tampilan dibuat ringan, jelas, dan tetap rapi ketika dibuka dari laptop, tablet, maupun handphone.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-4"><div class="card google-card feature-card h-100 border-0"><div class="card-body p-4"><div class="feature-icon mb-3"><i class="fa-solid fa-layer-group"></i></div><h5 class="fw-semibold">Kategori Dinamis</h5><p class="text-muted mb-0">Pertanyaan dikelompokkan otomatis per kategori seperti dokter, perawat, fasilitas, makanan, dan lainnya.</p></div></div></div>
                <div class="col-md-6 col-xl-4"><div class="card google-card feature-card h-100 border-0"><div class="card-body p-4"><div class="feature-icon mb-3"><i class="fa-solid fa-chart-line"></i></div><h5 class="fw-semibold">Analitik Admin</h5><p class="text-muted mb-0">Dashboard menampilkan grafik tren survei, distribusi kepuasan, dan kategori yang perlu perhatian lebih.</p></div></div></div>
                <div class="col-md-6 col-xl-4"><div class="card google-card feature-card h-100 border-0"><div class="card-body p-4"><div class="feature-icon mb-3"><i class="fa-solid fa-user-shield"></i></div><h5 class="fw-semibold">Akses Sesuai Role</h5><p class="text-muted mb-0">Admin mengelola pertanyaan dan user, sementara petugas fokus pada pemantauan hasil survei.</p></div></div></div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>


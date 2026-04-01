<?php
require_once __DIR__ . '/../config/koneksi.php';

if (!empty($_SESSION['user'])) {
    redirect_ke('admin/dashboard.php');
}

$error = get_flash('error');
$success = get_flash('success');
$pageTitle = 'Login Admin';
$bodyClass = 'login-page';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-lg-5 col-md-7">
            <div class="card google-card border-0 shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="login-icon mx-auto mb-3"><i class="fa-solid fa-user-doctor"></i></div>
                        <h2 class="fw-bold mb-1">Login Admin</h2>
                        <p class="text-muted mb-0">Masuk untuk mengelola survei kepuasan pasien.</p>
                    </div>
                    <?php if ($error) : ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
                    <?php if ($success) : ?><div class="alert alert-success"><?= e($success); ?></div><?php endif; ?>
                    <form action="<?= url('auth/proses_login.php'); ?>" method="post">
                        <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required autofocus></div>
                        <div class="mb-4"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                        <button type="submit" class="btn btn-rs-primary w-100"><i class="fa-solid fa-right-to-bracket me-2"></i>Login</button>
                    </form>
                    <div class="text-center mt-4 small text-muted">Pembuatan user baru dilakukan oleh admin dari panel dashboard.</div>
                    <div class="text-center mt-3"><a href="<?= url(); ?>" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Kembali ke halaman utama</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

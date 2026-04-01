<?php
$activePublic = $activePublic ?? '';
$stickyNav = $stickyNav ?? false;
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-rs shadow-sm <?= $stickyNav ? 'sticky-top' : ''; ?>">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= url(); ?>"><i class="fa-solid fa-hospital me-2"></i><?= APP_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navMenu">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <a class="nav-link <?= $activePublic === 'ralan' ? 'active' : ''; ?>" href="<?= url('survey/form_ralan.php'); ?>">Rawat Jalan</a>
                <a class="nav-link <?= $activePublic === 'ranap' ? 'active' : ''; ?>" href="<?= url('survey/form_ranap.php'); ?>">Rawat Inap</a>
                <a class="nav-link btn btn-light text-primary px-3 ms-lg-2 mt-2 mt-lg-0" href="<?= url('auth/login.php'); ?>">Login Admin</a>
            </div>
        </div>
    </div>
</nav>

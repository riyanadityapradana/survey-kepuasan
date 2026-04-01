<?php $activeMenu = $activeMenu ?? ''; ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-rs shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= url('admin/dashboard.php'); ?>"><i class="fa-solid fa-hospital me-2"></i>Admin Survei RSPI</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="adminNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : ''; ?>" href="<?= url('admin/dashboard.php'); ?>">Dashboard</a>
                <?php if (user_punya_role('admin')) : ?>
                    <a class="nav-link <?= $activeMenu === 'pertanyaan' ? 'active' : ''; ?>" href="<?= url('admin/pertanyaan/index.php'); ?>">Kelola Pertanyaan</a>
                    <a class="nav-link <?= $activeMenu === 'users' ? 'active' : ''; ?>" href="<?= url('admin/users/index.php'); ?>">Kelola User</a>
                <?php endif; ?>
                <a class="nav-link <?= $activeMenu === 'ralan' ? 'active' : ''; ?>" href="<?= url('admin/hasil/ralan.php'); ?>">Rawat Jalan</a>
                <a class="nav-link <?= $activeMenu === 'ranap' ? 'active' : ''; ?>" href="<?= url('admin/hasil/ranap.php'); ?>">Rawat Inap</a>
                <a class="nav-link" href="<?= url('auth/logout.php'); ?>">Logout</a>
            </div>
        </div>
    </div>
</nav>

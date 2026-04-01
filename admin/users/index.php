<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$users = mysqli_query($conn, 'SELECT id, username, role, created_at FROM users ORDER BY id DESC');
$success = get_flash('success');
$error = get_flash('error');
$pageTitle = 'Kelola User';
$activeMenu = 'users';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/admin_nav.php';
?>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card google-card h-100">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-1">Tambah User</h4>
                    <p class="text-muted mb-4">User baru hanya bisa dibuat oleh admin.</p>
                    <?php if ($error) : ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
                    <?php if ($success) : ?><div class="alert alert-success"><?= e($success); ?></div><?php endif; ?>
                    <form action="<?= url('auth/simpan_user.php'); ?>" method="post">
                        <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Konfirmasi Password</label><input type="password" name="konfirmasi_password" class="form-control" required></div>
                        <div class="mb-4"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="petugas">Petugas</option><option value="admin">Admin</option></select></div>
                        <button type="submit" class="btn btn-rs-primary w-100"><i class="fa-solid fa-user-plus me-2"></i>Simpan User</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card google-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="fw-bold mb-1">Daftar User</h4>
                            <p class="text-muted mb-0">Admin dapat menambah dan menghapus user.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Dibuat</th>
                                    <th width="90">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = mysqli_fetch_assoc($users)) : ?>
                                    <tr>
                                        <td><?= e($user['username']); ?></td>
                                        <td><span class="badge <?= $user['role'] === 'admin' ? 'text-bg-primary' : 'text-bg-secondary'; ?>"><?= strtoupper(e($user['role'])); ?></span></td>
                                        <td><?= !empty($user['created_at']) ? date('d-m-Y H:i', strtotime($user['created_at'])) : '-'; ?></td>
                                        <td>
                                            <?php if ((int) $user['id'] !== (int) user_saat_ini()['id']) : ?>
                                                <a href="<?= url('admin/users/hapus.php?id=' . $user['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini?')"><i class="fa-solid fa-trash"></i></a>
                                            <?php else : ?>
                                                <span class="text-muted small">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_ke('auth/login.php');
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    set_flash('error', 'Username dan password wajib diisi.');
    redirect_ke('auth/login.php');
}

$stmt = mysqli_prepare($conn, 'SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$loginValid = false;
if ($user) {
    if (password_verify($password, $user['password'])) {
        $loginValid = true;
    } elseif ($user['password'] === md5($password)) {
        $loginValid = true;
        $passwordBaru = password_hash($password, PASSWORD_DEFAULT);
        $update = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE id = ?');
        mysqli_stmt_bind_param($update, 'si', $passwordBaru, $user['id']);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
        $user['password'] = $passwordBaru;
    }
}

if (!$loginValid) {
    set_flash('error', 'Username atau password tidak sesuai.');
    redirect_ke('auth/login.php');
}

$_SESSION['user'] = [
    'id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
];

redirect_ke('admin/dashboard.php');
?>

<?php
require_once __DIR__ . '/../config/koneksi.php';

session_unset();
session_destroy();

session_start();
set_flash('success', 'Anda berhasil logout.');
redirect_ke('auth/login.php');
?>

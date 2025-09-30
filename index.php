<?php
// index.php - Halaman Awal / Dashboard Survey Kepuasan
include "koneksi.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Survey Kepuasan</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            margin: 0;
            background: #f6f6ff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #3949ab 60%, #5c6bc0 100%);
            color: #fff;
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            box-shadow: 2px 0 16px rgba(60,60,120,0.08);
            display: flex;
            flex-direction: column;
            z-index: 10;
        }
        .sidebar-header {
            font-size: 1.4em;
            font-weight: bold;
            padding: 32px 0 18px 0;
            text-align: center;
            letter-spacing: 1px;
        }
        .sidebar-menu {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 0 0 0;
        }
        .sidebar-menu a {
            color: #fff;
            text-decoration: none;
            padding: 12px 32px;
            font-size: 1.08em;
            border-left: 4px solid transparent;
            transition: background 0.2s, border-color 0.2s;
            display: block;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.10);
            border-left: 4px solid #ffeb3b;
        }
        .sidebar-footer {
            padding: 18px 0;
            text-align: center;
            font-size: 0.95em;
            color: #c5cae9;
        }
        .main-content {
            margin-left: 240px;
            padding: 48px 32px 32px 32px;
            min-height: 100vh;
        }
        @media (max-width: 700px) {
            .sidebar { width: 100vw; min-height: unset; height: 60px; flex-direction: row; align-items: center; position: static; }
            .sidebar-header, .sidebar-footer { display: none; }
            .sidebar-menu { flex-direction: row; gap: 0; width: 100vw; justify-content: space-around; }
            .sidebar-menu a { padding: 10px 8px; font-size: 1em; border-left: none; border-bottom: 2px solid transparent; }
            .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.10); border-bottom: 2px solid #ffeb3b; }
            .main-content { margin-left: 0; padding: 24px 4vw; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">Survey Kepuasan</div>
        <div class="sidebar-menu">
            <a href="survey.php">Isi Survey Kepuasan</a>
            <a href="laporan.php">Lihat Laporan Survey</a>
            <a href="login.php">Login Admin</a>
        </div>
        <div class="sidebar-footer">&copy; <?= date('Y') ?> Survey App</div>
    </div>
    <div class="main-content">
        <h1>Selamat Datang di Survey Kepuasan</h1>
        <p style="font-size:1.2em;max-width:600px;">Silakan gunakan menu di samping untuk mengisi survey, melihat laporan hasil survey, atau login sebagai admin untuk mengelola data survey dan pertanyaan.</p>
    </div>
</body>
</html>

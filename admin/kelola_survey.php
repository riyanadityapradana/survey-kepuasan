<?php
// admin/kelola_survey.php - CRUD Survey
session_start();
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }
include "../koneksi.php";
// Tambah survey
if (isset($_POST['tambah'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    mysqli_query($conn, "INSERT INTO surveys (judul_survey, deskripsi) VALUES ('$judul', '$deskripsi')");
    header('Location: kelola_survey.php'); exit;
}
// Hapus survey
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM survey WHERE id_survey=$id");
    header('Location: kelola_survey.php'); exit;
}
$surveys = mysqli_query($conn, "SELECT * FROM surveys ORDER BY id_survey DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Survey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar py-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-list-alt fa-2x mb-2"></i>
                    <h4 class="fw-bold mb-0">Admin Survey</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="kelola_survey.php"><i class="fa fa-list-alt me-2"></i> Kelola Survey</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_pertanyaan.php"><i class="fa fa-question-circle me-2"></i> Kelola Pertanyaan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../laporan.php"><i class="fa fa-chart-pie me-2"></i> Lihat Laporan</a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link" href="logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
                    </li>
                </ul>
            </nav>
            <main class="col-md-10 ms-sm-auto main-content py-4">
                <div class="container">
                    <h2 class="fw-bold mb-4"><i class="fa fa-list-alt me-2"></i> Kelola Survey</h2>
                    <form method="post" class="row g-2 mb-4">
                        <div class="col-md-5">
                            <input type="text" name="judul" class="form-control" placeholder="Judul Survey" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="tambah" class="btn btn-primary w-100">Tambah Survey</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tabelSurvey">
                            <thead class="table-light">
                                <tr><th>Judul</th><th>Deskripsi</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                            <?php while($s = mysqli_fetch_assoc($surveys)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['judul_survey']) ?></td>
                                    <td><?= htmlspecialchars($s['deskripsi']) ?></td>
                                    <td><a href="?hapus=<?= $s['id_survey'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus survey?')"><i class="fa fa-trash"></i> Hapus</a></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="dashboard.php" class="btn btn-secondary mt-3"><i class="fa fa-arrow-left me-2"></i> Kembali ke Dashboard</a>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>$(document).ready(function() { $('#tabelSurvey').DataTable(); });</script>
</body>
</html>

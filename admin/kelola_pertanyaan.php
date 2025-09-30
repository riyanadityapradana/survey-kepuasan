<?php
// admin/kelola_pertanyaan.php - CRUD Pertanyaan
session_start();
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }
include "../koneksi.php";
// Tambah pertanyaan
if (isset($_POST['tambah'])) {
    $id_survey = intval($_POST['id_survey']);
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    mysqli_query($conn, "INSERT INTO questions (id_survey, pertanyaan) VALUES ($id_survey, '$pertanyaan')");
    header('Location: kelola_pertanyaan.php'); exit;
}
// Hapus pertanyaan
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM questions WHERE id_question=$id");
    header('Location: kelola_pertanyaan.php'); exit;
}
$pertanyaan = mysqli_query($conn, "SELECT q.*, s.judul_survey FROM questions q LEFT JOIN surveys s ON q.id_survey=s.id_survey ORDER BY q.id_question DESC");
$surveys = mysqli_query($conn, "SELECT * FROM surveys ORDER BY id_survey DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pertanyaan</title>
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
                    <i class="fa-solid fa-question-circle fa-2x mb-2"></i>
                    <h4 class="fw-bold mb-0">Admin Survey</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_survey.php"><i class="fa fa-list-alt me-2"></i> Kelola Survey</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="kelola_pertanyaan.php"><i class="fa fa-question-circle me-2"></i> Kelola Pertanyaan</a>
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
                    <h2 class="fw-bold mb-4"><i class="fa fa-question-circle me-2"></i> Kelola Pertanyaan</h2>
                    <form method="post" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <select name="id_survey" class="form-select" required>
                                <option value="">Pilih Survey</option>
                                <?php while($s = mysqli_fetch_assoc($surveys)): ?>
                                <option value="<?= $s['id_survey'] ?>"><?= htmlspecialchars($s['judul_survey']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="pertanyaan" class="form-control" placeholder="Tulis pertanyaan" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="tambah" class="btn btn-primary w-100">Tambah Pertanyaan</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tabelPertanyaan">
                            <thead class="table-light">
                                <tr><th>Pertanyaan</th><th>Survey</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                            <?php while($q = mysqli_fetch_assoc($pertanyaan)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($q['pertanyaan']) ?></td>
                                    <td><?= htmlspecialchars($q['judul_survey']) ?></td>
                                    <td><a href="?hapus=<?= $q['id_question'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pertanyaan?')"><i class="fa fa-trash"></i> Hapus</a></td>
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
    <script>$(document).ready(function() { $('#tabelPertanyaan').DataTable(); });</script>
</body>
</html>

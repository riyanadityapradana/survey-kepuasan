<?php
// admin/dashboard.php - Dashboard admin (dummy, bisa dikembangkan)
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .status-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar py-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-chart-bar fa-2x mb-2"></i>
                    <h4 class="fw-bold mb-0">Admin Survey</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_survey.php"><i class="fa fa-list-alt me-2"></i> Kelola Survey</a>
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
            <main class="col-md-10 ms-sm-auto main-content d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 520px;">
                    <div class="text-center p-4 bg-white rounded-4 shadow-sm">
                        <h1 class="fw-bold mb-4">Admin Dashboard</h1>
                        <div class="d-grid gap-3">
                            <a href="kelola_survey.php" class="btn btn-primary btn-lg"><i class="fa fa-list-alt me-2"></i> Kelola Survey</a>
                            <a href="kelola_pertanyaan.php" class="btn btn-primary btn-lg"><i class="fa fa-question-circle me-2"></i> Kelola Pertanyaan</a>
                            <a href="../laporan.php" class="btn btn-primary btn-lg"><i class="fa fa-chart-pie me-2"></i> Lihat Laporan</a>
                            <a href="logout.php" class="btn btn-danger btn-lg"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>

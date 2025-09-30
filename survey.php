<?php
// survey.php - Tampilkan daftar pertanyaan survey
include "koneksi.php";
$query = mysqli_query($conn, "SELECT * FROM questions ORDER BY id_question ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Kepuasan</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js" defer></script>
    <style>
        .survey-panel-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2vw;
            justify-content: center;
            margin: 40px 0 30px 0;
        }
        .survey-card {
            min-width: 260px;
            max-width: 340px;
            background: #f5faff;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.08);
            padding: 2em 2vw 1.5em 2vw;
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: box-shadow 0.2s, transform 0.2s;
            border-left: 8px solid #1976d2;
        }
        .survey-card:nth-child(2n) { border-left-color: #388e3c; }
        .survey-card:nth-child(3n) { border-left-color: #fbc02d; }
        .survey-card:hover {
            box-shadow: 0 8px 32px rgba(60,60,180,0.13);
            transform: translateY(-4px) scale(1.03);
        }
        .survey-card-title {
            font-size: 1.25em;
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 0.7em;
        }
        .survey-card-link {
            display: inline-block;
            padding: 10px 22px;
            background: #1976d2;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 1em;
            margin-top: 0.5em;
            transition: background 0.2s;
        }
        .survey-card:nth-child(2n) .survey-card-link { background: #388e3c; }
        .survey-card:nth-child(3n) .survey-card-link { background: #fbc02d; color: #222; }
        .survey-card-link:hover { filter: brightness(0.9); }
        @media (max-width: 900px) {
            .survey-panel-container { flex-direction: column; align-items: center; }
            .survey-card { width: 98vw; max-width: 99vw; }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width:1400px;">
        <h1 style="text-align:center;">Survey Kepuasan</h1>
        <p style="text-align:center;">Silakan pilih pertanyaan untuk memberikan penilaian:</p>
        <div class="survey-panel-container">
            <?php $i=0; while($row = mysqli_fetch_assoc($query)): $i++; ?>
            <div class="survey-card">
                <div class="survey-card-title"><?= htmlspecialchars($row['pertanyaan']) ?></div>
                <a href="question.php?id=<?= $row['id_question'] ?>" class="survey-card-link">Isi Penilaian</a>
            </div>
            <?php endwhile; ?>
        </div>
        <div style="text-align:center;margin-top:2em;">
            <a href="index.php" class="back-link">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>

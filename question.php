<?php
// question.php - Tampilkan 1 pertanyaan dan pilihan emot
include "koneksi.php";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$q = mysqli_query($conn, "SELECT * FROM questions WHERE id_question=$id");
$pertanyaan = mysqli_fetch_assoc($q);
if (!$pertanyaan) {
    echo '<div class="container"><h2>Pertanyaan tidak ditemukan.</h2></div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey: <?= htmlspecialchars($pertanyaan['pertanyaan']) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js" defer></script>
    <style>
        .container {
            max-width: 1600px;
            margin: 100px auto;
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 2px 32px rgba(0,0,0,0.10);
            padding: 60px 48px 48px 48px;
        }
        .question-title {
            font-size: 3em;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 0.2em;
            margin-top: 0.5em;
            text-align: center;
        }
        .question-subtitle {
            font-size: 1.3em;
            color: #3949ab;
            text-align: center;
            margin-bottom: 2em;
        }
        .emot-row {
            display: flex;
            justify-content: center;
            gap: 3vw;
            margin-bottom: 2em;
            flex-wrap: wrap;
        }
        .emot-card {
            background: #fffffa;
            border-radius: 24px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.09);
            padding: 3em 2vw 2em 2vw;
            min-width: 340px;
            max-width: 400px;
            text-align: center;
            transition: box-shadow 0.2s, transform 0.2s;
            border: none;
            cursor: pointer;
        }
        .emot-card:hover {
            box-shadow: 0 8px 24px rgba(60,60,180,0.13);
            transform: translateY(-4px) scale(1.04);
        }
        .emot-emoji {
            font-size: 5.5em;
            margin-bottom: 0.3em;
        }
        .emot-label {
            font-size: 1.7em;
            font-weight: 600;
            color: #1a237e;
        }
        @media (max-width: 1400px) {
            .container { max-width: 98vw; padding: 18px 2vw; }
            .emot-row { gap: 1.5vw; }
            .emot-card { min-width: 220px; max-width: 260px; padding: 2em 1vw; }
            .emot-emoji { font-size: 3em; }
            .emot-label { font-size: 1.2em; }
        }
        @media (max-width: 900px) {
            .container { max-width: 99vw; padding: 8px 1vw; }
            .question-title { font-size: 1.5em; }
            .emot-row { gap: 1vw; }
            .emot-card { min-width: 120px; max-width: 140px; padding: 1em 1vw; }
            .emot-emoji { font-size: 2em; }
            .emot-label { font-size: 1em; }
        }
    </style>
</head>
<body>
    <div class="container" style="background:#f6f6ff;">
    <div class="question-title"><?= htmlspecialchars($pertanyaan['pertanyaan']) ?></div>
        <div class="question-subtitle">Pilih salah satu emoji di bawah ini</div>
        <form action="simpan_single.php" method="post">
            <input type="hidden" name="id_question" value="<?= $id ?>">
            <div class="emot-row">
                <button type="submit" name="jawaban" value="senang" class="emot-card">
                    <div class="emot-emoji">😊</div>
                    <div class="emot-label">Puas</div>
                </button>
                <button type="submit" name="jawaban" value="biasa" class="emot-card">
                    <div class="emot-emoji">😐</div>
                    <div class="emot-label">Biasa saja</div>
                </button>
                <button type="submit" name="jawaban" value="buruk" class="emot-card">
                    <div class="emot-emoji">😞</div>
                    <div class="emot-label">Tidak puas</div>
                </button>
            </div>
        </form>
        <div style="text-align:center;color:#3949ab;margin-top:2em;font-size:1em;">Ketuk salah satu emoji untuk memberikan feedback</div>
        <a href="survey.php" class="back-link" style="margin-top:2em;display:inline-block;">Kembali ke Survey</a>
    </div>
</body>
</html>

<?php
// terimakasih.php - Pop up pesan setelah submit jawaban
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .modal-thanks-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(30,30,60,0.12);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-thanks {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.13);
            padding: 48px 36px 36px 36px;
            text-align: center;
            min-width: 340px;
            max-width: 90vw;
            animation: popin 0.3s cubic-bezier(.68,-0.55,.27,1.55);
        }
        @keyframes popin {
            0% { transform: scale(0.7); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .thanks-icon {
            font-size: 4em;
            margin-bottom: 0.3em;
            color: #4caf50;
        }
        .thanks-title {
            font-size: 2em;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 0.5em;
        }
        .thanks-desc {
            color: #3949ab;
            font-size: 1.1em;
            margin-bottom: 1.5em;
        }
    </style>
    <script>
        setTimeout(function(){
            window.location.href = "question.php?id=<?= $id ?>";
        }, 3000);
    </script>
</head>
<body style="background:#f6f6ff;">
    <div class="modal-thanks-bg">
        <div class="modal-thanks">
            <div class="thanks-icon">🙏</div>
            <div class="thanks-title">Terima kasih atas feedback Anda!</div>
            <div class="thanks-desc">Feedback Anda sangat berarti untuk kami.<br>Anda akan diarahkan kembali dalam 3 detik...</div>
        </div>
    </div>
</body>
</html>

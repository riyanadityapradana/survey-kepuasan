<?php
// simpan_single.php - Proses simpan 1 jawaban
include "koneksi.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_question'], $_POST['jawaban'])) {
    $id_question = intval($_POST['id_question']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    // Buat entry response baru
    mysqli_query($conn, "INSERT INTO responses (created_at) VALUES (NOW())");
    $id_response = mysqli_insert_id($conn);
    // Simpan jawaban
    mysqli_query($conn, "INSERT INTO answers (id_response, id_question, jawaban) VALUES ($id_response, $id_question, '$jawaban')");
    header("Location: terimakasih.php?id=$id_question");
    exit;
} else {
    header("Location: survey.php");
    exit;
}
?>

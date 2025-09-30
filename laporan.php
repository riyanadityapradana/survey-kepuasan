
<?php
// laporan.php - Laporan hasil survey (tabel + grafik, filter 1 pertanyaan)
include "koneksi.php";
$questions = mysqli_query($conn, "SELECT * FROM questions ORDER BY id_question ASC");
$id_question = isset($_GET['id']) ? intval($_GET['id']) : 0;
$q = false;
if ($id_question) {
    $qres = mysqli_query($conn, "SELECT * FROM questions WHERE id_question=$id_question");
    $q = mysqli_fetch_assoc($qres);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Survey Kepuasan</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jsPDF & autoTable for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    <div class="container" style="max-width:1700px;min-width:950px;">
        <h1>Laporan Survey Kepuasan</h1>
        <form method="get" style="margin-bottom:24px;text-align:center;">
            <label for="id" style="font-weight:600;">Pilih Pertanyaan:</label>
            <select name="id" id="id" onchange="this.form.submit()" style="padding:12px 32px;border-radius:12px;margin-left:12px;min-width:500px;font-size:1.15em;">
                <option value="">-- Pilih --</option>
                <?php mysqli_data_seek($questions, 0); while($row = mysqli_fetch_assoc($questions)): ?>
                <option value="<?= $row['id_question'] ?>" <?= $id_question==$row['id_question']?'selected':'' ?>><?= htmlspecialchars($row['pertanyaan']) ?></option>
                <?php endwhile; ?>
            </select>
        </form>
        <?php if ($q): ?>
        <div class="report-block" style="max-width:900px;margin:0 auto;">
            <h3 style="font-size:1.3em;"><?= htmlspecialchars($q['pertanyaan']) ?></h3>
            <div style="text-align:right;margin-bottom:10px;">
                <button onclick="exportTableToCSV('laporan-survey.csv')" style="padding:8px 18px;margin-right:8px;font-size:1em;">Export CSV</button>
                <button onclick="exportTableToPDF()" style="padding:8px 18px;font-size:1em;">Export PDF</button>
            </div>
            <table id="laporanTable" border="1" cellpadding="10" style="margin:18px auto;min-width:900px;font-size:1.15em;">
                <tr><th>Jawaban</th><th>Total</th></tr>
                <?php
                $data = ['senang'=>0,'biasa'=>0,'buruk'=>0];
                $res = mysqli_query($conn, "SELECT jawaban, COUNT(*) as total FROM answers WHERE id_question=".$q['id_question']." GROUP BY jawaban");
                while($row = mysqli_fetch_assoc($res)) {
                    $data[$row['jawaban']] = $row['total'];
                    echo "<tr><td>".ucfirst($row['jawaban'])."</td><td>".$row['total']."</td></tr>";
                }
                ?>
            </table>
            <div style="width:100%;max-width:900px;margin:0 auto;">
                <canvas id="chart<?= $q['id_question'] ?>" height="160"></canvas>
            </div>
            <script>
            new Chart(document.getElementById('chart<?= $q['id_question'] ?>'), {
                type: 'bar',
                data: {
                    labels: ['Senang', 'Biasa', 'Buruk'],
                    datasets: [{
                        label: 'Jumlah',
                        data: [<?= $data['senang'] ?>, <?= $data['biasa'] ?>, <?= $data['buruk'] ?>],
                        backgroundColor: ['#4caf50','#ffeb3b','#f44336']
                    }]
                },
                options: {responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
            });
            // Export CSV
            function exportTableToCSV(filename) {
                var csv = [];
                var rows = document.querySelectorAll("#laporanTable tr");
                for (var i = 0; i < rows.length; i++) {
                    var row = [], cols = rows[i].querySelectorAll("td, th");
                    for (var j = 0; j < cols.length; j++)
                        row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                    csv.push(row.join(","));
                }
                var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
                var downloadLink = document.createElement("a");
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            }
            // Export PDF
            function exportTableToPDF() {
                var { jsPDF } = window.jspdf;
                var doc = new jsPDF();
                doc.text("Laporan Survey Kepuasan", 14, 14);
                doc.text("<?= str_replace('"', '\"', htmlspecialchars($q['pertanyaan'])) ?>", 14, 24);
                doc.autoTable({
                    html: '#laporanTable',
                    startY: 30,
                    headStyles: {fillColor: [41,128,185]},
                    styles: {fontSize:12, cellPadding:3}
                });
                doc.save('laporan-survey.pdf');
            }
            </script>
        </div>
        <?php elseif($id_question): ?>
            <div style="text-align:center;color:#b71c1c;">Pertanyaan tidak ditemukan.</div>
        <?php endif; ?>
        <div style="text-align:center;margin-top:2em;">
            <a href="index.php" class="back-link" style="padding:14px 40px;font-size:1.15em;">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>

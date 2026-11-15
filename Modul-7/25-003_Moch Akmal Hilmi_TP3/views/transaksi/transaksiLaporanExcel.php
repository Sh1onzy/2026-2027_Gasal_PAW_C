<?php
// Dipanggil lewat proses.php (route /transaksi/laporanExcel).
// $conn sudah tersedia dari index.php.

// Buang seluruh output layout (doctype, nav, dst.) yang sudah di-buffer index.php
while (ob_get_level() > 0) {
    ob_end_clean();
}

$tglMulai   = trim($_GET['tanggal_mulai'] ?? '');
$tglSelesai = trim($_GET['tanggal_selesai'] ?? '');

$rekap = [];
$pesan = '';

if ($tglMulai === '' || $tglSelesai === '') {
    $pesan = 'Rentang tanggal belum dipilih.';
} elseif ($tglMulai > $tglSelesai) {
    $pesan = 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.';
} else {
    try {
        $stmt = $conn->prepare("
            SELECT tanggal, SUM(total) AS total_pendapatan
            FROM transaksi
            WHERE tanggal BETWEEN ? AND ?
            GROUP BY tanggal
            ORDER BY tanggal ASC
        ");
        $stmt->bind_param("ss", $tglMulai, $tglSelesai);
        $stmt->execute();
        $rekap = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        $pesan = "ERROR : " . $e->getMessage();
    }
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=laporanPenjualan_{$tglMulai}_{$tglSelesai}.xls");
?>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
<?php if ($pesan !== ''): ?>
    <p><?= htmlspecialchars($pesan); ?></p>
<?php elseif (empty($rekap)): ?>
    <p>Tidak ada transaksi pada rentang tanggal tersebut.</p>
<?php else: ?>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Total Pendapatan</th>
                <th>Tanggal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($rekap as $row): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= (int)$row['total_pendapatan']; ?></td>
                    <td><?= htmlspecialchars((string)$row['tanggal']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</body>
</html>
<?php exit; ?>

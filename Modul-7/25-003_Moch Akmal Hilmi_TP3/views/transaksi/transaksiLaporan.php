<?php
$tglMulai   = trim($_GET['tanggal_mulai'] ?? '');
$tglSelesai = trim($_GET['tanggal_selesai'] ?? '');
$sudahFilter = $tglMulai !== '' && $tglSelesai !== '';

$error     = '';
$rekap     = [];
$ringkasan = ['jml_pelanggan' => 0, 'total_keseluruhan' => 0];

if ($sudahFilter) {
    if ($tglMulai > $tglSelesai) {
        $error = "Tanggal mulai tidak boleh lebih besar dari tanggal selesai.";
    } else {
        try {
            // Rekap harian (untuk grafik dan tabel)
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

            // Ringkasan kumulatif (untuk card)
            $stmt2 = $conn->prepare("
                SELECT COUNT(DISTINCT pelanggan) AS jml_pelanggan,
                       COALESCE(SUM(total), 0)   AS total_keseluruhan
                FROM transaksi
                WHERE tanggal BETWEEN ? AND ?
            ");
            $stmt2->bind_param("ss", $tglMulai, $tglSelesai);
            $stmt2->execute();
            $ringkasan = $stmt2->get_result()->fetch_assoc() ?? $ringkasan;
        } catch (Exception $e) {
            $error = "ERROR : " . $e->getMessage();
        }
    }
}

// Data untuk Chart.js
$labels    = [];
$dataTotal = [];
foreach ($rekap as $r) {
    $labels[]    = $r['tanggal'];
    $dataTotal[] = (int)$r['total_pendapatan'];
}

// Link Excel dengan membawa rentang tanggal yang sama
$queryExcel = http_build_query([
    'url'             => '/transaksi/laporanExcel',
    'tanggal_mulai'   => $tglMulai,
    'tanggal_selesai' => $tglSelesai,
]);
?>
<style>
    .filter-form {
        display: flex;
        gap: 12px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .filter-form label {
        flex: 1;
        min-width: 180px;
        margin-bottom: 0;
    }
    .aksi-laporan {
        display: flex;
        gap: 8px;
        margin-top: 16px;
        flex-wrap: wrap;
    }
    .card-wrap {
        display: flex;
        gap: 16px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    .card-ringkasan {
        flex: 1;
        min-width: 220px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        background-color: #f8fafc;
    }
    .card-ringkasan .judul {
        font-size: 0.85rem;
        color: #64748b;
    }
    .card-ringkasan .nilai {
        font-size: 1.4rem;
        font-weight: 600;
        color: #0f172a;
    }

    @media print {
        nav, .no-print { display: none !important; }
        body { background-color: #ffffff; }
        main {
            max-width: none;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
        }
        table, th, td { border-color: #000 !important; }
        .card-wrap, .card-ringkasan, table, canvas {
            break-inside: avoid;
        }
    }
</style>

<h2>Laporan Penjualan</h2>

<form method="GET" class="filter-form no-print">
    <input type="hidden" name="url" value="/transaksi/laporan">
    <label>Tanggal Mulai :
        <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($tglMulai); ?>" required>
    </label>
    <label>Tanggal Selesai :
        <input type="date" name="tanggal_selesai" value="<?= htmlspecialchars($tglSelesai); ?>" required>
    </label>
    <button type="submit">Tampilkan</button>
</form>

<?php if ($error !== ''): ?>
    <p style="margin-top: 1rem; color: #dc2626;"><?= htmlspecialchars($error); ?></p>

<?php elseif (!$sudahFilter): ?>
    <p style="margin-top: 1rem; color: #64748b;">Pilih rentang tanggal lalu klik "Tampilkan".</p>

<?php elseif (empty($rekap)): ?>
    <p style="margin-top: 1rem; color: #64748b;">Tidak ada transaksi pada rentang tanggal tersebut.</p>

<?php else: ?>
    <div class="aksi-laporan no-print">
        <button type="button" onclick="window.print()">Cetak</button>
        <a href="?<?= htmlspecialchars($queryExcel); ?>" target="_blank">Excel</a>
    </div>

    <div style="margin-top: 20px; max-width: 900px;">
        <canvas id="chartPendapatan"></canvas>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60px;">No</th>
                <th>Total Pendapatan</th>
                <th>Tanggal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($rekap as $row): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td>Rp <?= number_format((float)$row['total_pendapatan'], 0, ',', '.'); ?></td>
                    <td><?= htmlspecialchars((string)$row['tanggal']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="card-wrap">
        <div class="card-ringkasan">
            <div class="judul">Jumlah Pelanggan Unik</div>
            <div class="nilai"><?= (int)$ringkasan['jml_pelanggan']; ?> Orang</div>
        </div>
        <div class="card-ringkasan">
            <div class="judul">Total Pendapatan Keseluruhan</div>
            <div class="nilai">Rp <?= number_format((float)$ringkasan['total_keseluruhan'], 0, ',', '.'); ?></div>
        </div>
    </div>

    <script>
        const dataRekap  = <?= json_encode($dataTotal); ?>;
        const labelRekap = <?= json_encode($labels); ?>;

        new Chart(document.getElementById('chartPendapatan'), {
            type: 'bar',
            data: {
                labels: labelRekap,
                datasets: [{
                    label: 'Total Pendapatan (Rp)',
                    data: dataRekap,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Tanggal Transaksi' } },
                    y: { beginAtZero: true, title: { display: true, text: 'Total Pendapatan (Rp)' } }
                }
            }
        });
    </script>
<?php endif; ?>

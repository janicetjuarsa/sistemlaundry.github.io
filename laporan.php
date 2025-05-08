<?php
include 'db.php'; // koneksi utama
$reportType = $_GET['reportType'] ?? 'keuangan';

function laporan_keuangan($conn) {
  $query = "
    SELECT o.created_at, o.id, (o.berat * l.harga_per_kg) AS total
    FROM orders o
    JOIN layanan l ON o.layanan = l.jenis_layanan
    ORDER BY o.created_at DESC
  ";
  $result = $conn->query($query);
  echo "<thead><tr><th>Tanggal</th><th>Kode Transaksi</th><th>Total (Rp)</th></tr></thead><tbody>";
  $total = $max = 0; $count = 0;
  while ($row = $result->fetch_assoc()) {
    $jumlah = $row['total'];
    echo "<tr><td>{$row['created_at']}</td><td>{$row['id']}</td><td>Rp ".number_format($jumlah, 0, ',', '.')."</td></tr>";
    $total += $jumlah; $count++;
    if ($jumlah > $max) $max = $jumlah;
  }
  echo "</tbody>";
  echo "<script>
    document.getElementById('summary1').innerText = 'Total: Rp ".number_format($total, 0, ',', '.')."';
    document.getElementById('summary2').innerText = 'Rata-rata: Rp ".number_format($count ? $total/$count : 0, 0, ',', '.')."';
    document.getElementById('summary3').innerText = 'Tertinggi: Rp ".number_format($max, 0, ',', '.')."';
  </script>";
}

function jumlah_transaksi($conn) {
  $query = "
    SELECT DATE(created_at) AS tanggal, COUNT(*) AS jumlah
    FROM orders
    GROUP BY DATE(created_at)
    ORDER BY tanggal DESC
  ";
  $result = $conn->query($query);
  echo "<thead><tr><th>Tanggal</th><th>Jumlah Transaksi</th></tr></thead><tbody>";
  while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['tanggal']}</td><td>{$row['jumlah']}</td></tr>";
  }
  echo "</tbody>";
}

function aktivitas_pelanggan($conn) {
  $query = "
    SELECT u.id, u.username, COUNT(o.id) AS jumlah
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    ORDER BY jumlah DESC
  ";
  $result = $conn->query($query);
  echo "<thead><tr><th>Kode Pelanggan</th><th>Nama Pelanggan</th><th>Jumlah Order</th></tr></thead><tbody>";
  while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['jumlah']}</td></tr>";
  }
  echo "</tbody>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Statistik - DripLess</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; padding: 20px; }
    .container {
      max-width: 1100px;
      margin: auto;
      background: #e6f2fb;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h1 { text-align: center; color: #2a3d66; margin-bottom: 30px; }
    .filters {
      display: flex; justify-content: space-between; gap: 20px; margin-bottom: 30px;
    }
    select {
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      flex: 1;
    }
    button {
      padding: 10px 20px;
      background-color: #2a9d8f;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }
    .summary {
      display: flex;
      justify-content: space-around;
      margin-top: 20px;
    }
    .summary div {
      background: white;
      padding: 20px;
      border-radius: 10px;
      width: 25%;
      text-align: center;
      font-size: 18px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
      background-color: white;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: center;
    }
    th {
      background-color: #d1e9ff;
    }
    .back-btn {
      display: inline-block;
      margin-top: 20px;
      background-color: #a4cbee;
      color: white;
      padding: 10px 20px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: bold;
    }
    .back-btn:hover {
      background-color: #90b8dd;
    }
  </style>
</head>
<body>
<div class="container">
  <h1>Laporan Statistik DripLess</h1>
  <div class="filters">
    <form method="GET" style="display:flex; gap:10px; width:100%">
      <select name="reportType">
        <option value="keuangan" <?= $reportType == 'keuangan' ? 'selected' : '' ?>>Laporan Keuangan</option>
        <option value="transaksi" <?= $reportType == 'transaksi' ? 'selected' : '' ?>>Jumlah Transaksi</option>
        <option value="aktivitas" <?= $reportType == 'aktivitas' ? 'selected' : '' ?>>Aktivitas Pelanggan</option>
      </select>
      <button type="submit">Tampilkan</button>
    </form>
  </div>

  <div class="summary">
    <div id="summary1">Total: -</div>
    <div id="summary2">Rata-rata: -</div>
    <div id="summary3">Tertinggi: -</div>
  </div>

  <table id="reportTable">
    <?php
      if ($reportType == 'keuangan') laporan_keuangan($conn);
      else if ($reportType == 'transaksi') jumlah_transaksi($conn);
      else if ($reportType == 'aktivitas') aktivitas_pelanggan($conn);
    ?>
  </table>

  <a href="homepage_admin.php" class="back-btn">Back</a>
</div>
</body>
</html>

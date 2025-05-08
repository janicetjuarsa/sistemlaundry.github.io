<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "laundry");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Ambil data pesanan + total harga dari join layanan, hanya untuk user yang sedang login
$stmt = $conn->prepare("
  SELECT o.*, l.harga_per_kg, l.jenis_layanan, (o.berat * l.harga_per_kg) AS total_harga, u.username
  FROM orders o
  JOIN layanan l ON o.layanan = l.jenis_layanan
  JOIN users u ON u.id = o.user_id
  WHERE o.user_id = ?
  ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

// Ambil user dan order terbaru
$latest_order = $orders->fetch_assoc(); // baris pertama = order terbaru
$user = ['username' => $latest_order['username']]; // ambil username

// Debugging: cek apakah $latest_order ada datanya
// var_dump($latest_order); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - DripLess</title>
  <link rel="stylesheet" href="homepage.css">
</head>
<body>
  <div class="container">
    <header>
      <img src="gambar/logo.png" alt="DripLess Logo" class="logo">
      <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
      <div class="header-right">
        <img src="gambar/icons8-test-account-80.png" alt="Profile Picture" class="profile-pic">
      </div>
      <button class="logout"><a href="index.html">Logout</a></button>
    </header>

    <section class="status-section">
      <div class="card">
        <h2>Status Saat Ini</h2>
        <!-- Menampilkan status pesanan terbaru sesuai user -->
        <p><?= $latest_order ? htmlspecialchars($latest_order['status']) : 'Tidak ada pesanan' ?></p>
      </div>
      <div class="card">
        <h2>Estimasi Selesai</h2>
        <!-- Menampilkan estimasi selesai pesanan terbaru sesuai user -->
        <p><?= $latest_order && $latest_order['waktu_penjemputan'] ? date('d M Y H:i', strtotime($latest_order['waktu_penjemputan'])) : 'Belum ada estimasi' ?></p>
      </div>
      <div class="card">
        <h2>Layanan</h2>
        <!-- Menampilkan layanan pesanan terbaru sesuai user -->
        <p><?= $latest_order ? htmlspecialchars($latest_order['jenis_layanan']) : 'Tidak ada layanan' ?></p>
      </div>
    </section>

    <section class="order-section">
      <h2>Detail Pemesanan</h2>
      <table>
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Layanan</th>
            <th>Berat (kg)</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($orders->num_rows > 0):
              mysqli_data_seek($orders, 0); // reset pointer ke awal
              while ($row = $orders->fetch_assoc()):
          ?>
            <tr>
              <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
              <td><?= htmlspecialchars($row['jenis_layanan']) ?></td>
              <td><?= htmlspecialchars($row['berat']) ?></td>
              <td>Rp<?= number_format($row['total_harga'], 0, ',', '.') ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
          <?php endwhile; else: ?>
            <tr>
              <td colspan="5">Belum ada pesanan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

    <section class="cta">
      <button class="pesan-btn"><a href="pesan.php">Pesan Layanan</a></button>
    </section>
  </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

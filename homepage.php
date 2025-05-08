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

// Ambil username dari tabel users berdasarkan session
$user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

$user = ['username' => $user_data ? $user_data['username'] : 'User'];
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
      <!-- Menampilkan status pesanan untuk semua pesanan yang dimiliki user -->
      <?php if ($orders->num_rows > 0): ?>
        <ul>
          <?php
          // Reset pointer ke awal data dan loop untuk menampilkan status
          mysqli_data_seek($orders, 0);
          while ($row = $orders->fetch_assoc()):
          ?>
            <li>
              <strong>Status Layanan <?= $row['layanan'] ?>:</strong> <?= htmlspecialchars($row['status']) ?>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>Tidak ada pesanan.</p>
      <?php endif; ?>
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
              <td><a href="tracking.php?id=<?= $row['id'] ?>" style="color:black"><?= htmlspecialchars($row['status']) ?></a></td>
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

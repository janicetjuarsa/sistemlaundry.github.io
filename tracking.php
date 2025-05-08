<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "laundry");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Ambil id pesanan dari parameter URL
$order_id = isset($_GET['id']) ? $_GET['id'] : null;

// Pastikan id pesanan ada dan valid
if ($order_id) {
    // Ambil data pesanan berdasarkan id dan user_id
    $stmt = $conn->prepare("
      SELECT o.*, l.jenis_layanan
      FROM orders o
      JOIN layanan l ON o.layanan = l.jenis_layanan
      WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
} else {
    $order = null;
}

$stepActive = 0;
if ($order) {
    $status = $order['status'];

    if ($status === "Menunggu Penjemputan") $stepActive = 1;
    elseif ($status === "Dalam Penjemputan") $stepActive = 2;
    elseif ($status === "Sedang Diproses") $stepActive = 3;
    elseif ($status === "Selesai") $stepActive = 4;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tracking Pesanan - DripLess</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-image: url('background.png');
      background-size: cover;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      background-color: #e6f2fb;
      margin: 30px auto;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
    }

    h1 {
      color: #2a3d66;
      margin-bottom: 25px;
    }

    .progress-container {
      display: flex;
      justify-content: space-between;
      margin: 30px 0;
      position: relative;
    }

    .progress-container::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 8%;
      width: 84%;
      height: 4px;
      background-color: #a4cbee;
      z-index: 0;
    }

    .step {
      background-color: #cde2f7;
      border: 3px solid #a4cbee;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      z-index: 1;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: bold;
    }

    .step.active {
      background-color: #a4cbee;
      color: white;
    }

    .status-info {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      margin-top: 25px;
      text-align: left;
    }

    .back-btn {
      display: inline-block;
      margin-top: 25px;
      padding: 12px 25px;
      background-color: #a4cbee;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
    }

    .back-btn:hover {
      background-color: #90b8dd;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Tracking Pesanan Anda</h1>

    <div class="progress-container">
      <div class="step <?= $stepActive >= 1 ? 'active' : '' ?>">1<br>Pesanan</div>
      <div class="step <?= $stepActive >= 2 ? 'active' : '' ?>">2<br>Jemput</div>
      <div class="step <?= $stepActive >= 3 ? 'active' : '' ?>">3<br>Proses</div>
      <div class="step <?= $stepActive >= 4 ? 'active' : '' ?>">4<br>Selesai</div>
    </div>

    <?php if ($order): ?>
    <div class="status-info">
      <p><strong>ID Pesanan:</strong> #<?= htmlspecialchars($order['id']) ?></p>
      <p><strong>Jenis Layanan:</strong> <?= htmlspecialchars($order['jenis_layanan']) ?></p>
      <p><strong>Status Saat Ini:</strong> <?= htmlspecialchars($order['status']) ?></p>
      <p><strong>Estimasi Selesai:</strong>
        <?= $order['waktu_penjemputan'] ? date('d M Y, H:i', strtotime($order['waktu_penjemputan'])) . ' WIB' : 'Belum tersedia' ?>
      </p>
    </div>
    <?php else: ?>
    <div class="status-info">
      <p>Tidak ada pesanan yang ditemukan.</p>
    </div>
    <?php endif; ?>

    <a href="homepage.php" class="back-btn">Kembali ke Dashboard</a>
  </div>
</body>
</html>

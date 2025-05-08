<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "laundry";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Update status jika dikirim melalui POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id'], $_POST['new_status'])) {
    $id = $_POST['order_id'];
    $status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: homepage_admin.php");
    exit;
}

// Ambil semua orders + username
$query = "SELECT orders.id, users.username, orders.layanan, orders.berat, orders.lokasi, 
                 orders.waktu_penjemputan, orders.status 
          FROM orders
          JOIN users ON orders.user_id = users.id
          ORDER BY orders.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - DripLess</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-image: url('background.png');
      background-size: cover;
      margin: 0;
      padding: 0;
    }

    h1{
    padding-left: 190px;
  }

    .container {
      max-width: 1000px;
      background-color: #e6f2fb;
      margin: 40px auto;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      height: 70px;
    }

    .logo {
      width: 50px;
    }

    h1 {
      color: #2a3d66;
    }

    .logout {
      background-color: #a4cbee;
      border: none;
      color: white;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #cde2f7;
    }

    select {
      padding: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .logo {
    width: 170px;
    height: 70px;
  }

    .nav {
      margin-top: 25px;
      text-align: center;
    }

    .nav a {
      text-decoration: none;
      margin: 0 10px;
      background-color: #a4cbee;
      padding: 10px 20px;
      border-radius: 8px;
      color: white;
      font-weight: bold;
    }

    .nav a:hover {
      background-color: #6189e6;
    }

    .logout {
    background-color: #a4cbee;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
    margin-right: 5px;
    font-weight: bold;
  }
  
  .profile-pic{
    height:50px;
    width: 50px;
    margin-left: 150px;
  }

  a{
    text-decoration: none;
    color: white;
  }

  .logout:hover{
    background-color: #6189e6;
  }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="gambar/logo.png" alt="DripLess Logo" class="logo">
      <h1>Dashboard Admin</h1>
      <div class="header-right">
        <img src="gambar/icons8-test-account-80.png" alt="Profile Picture" class="profile-pic">
      </div>
      <button class="logout"><a href="index.html">Logout</a></button>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID Pesanan</th>
          <th>Nama</th>
          <th>Layanan</th>
          <th>Berat</th>
          <th>Alamat</th>
          <th>Waktu Jemput</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td>DL<?= str_pad($row['id'], 6, "0", STR_PAD_LEFT); ?></td>
              <td><?= htmlspecialchars($row['username']); ?></td>
              <td><?= htmlspecialchars($row['layanan']); ?></td>
              <td><?= htmlspecialchars($row['berat']); ?> kg</td>
              <td><?= htmlspecialchars($row['lokasi']); ?></td>
              <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($row['waktu_penjemputan']))); ?></td>
              <td>
                <form method="POST">
                  <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                  <select name="new_status" onchange="this.form.submit()">
                    <?php
                      $statuses = ['Menunggu Penjemputan', 'Dalam Penjemputan', 'Sedang Diproses', 'Selesai'];
                      foreach ($statuses as $status) {
                        $selected = ($status == $row['status']) ? 'selected' : '';
                        echo "<option value=\"$status\" $selected>$status</option>";
                      }
                    ?>
                  </select>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" style="text-align:center;">No Orders</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="nav">
      <a href="layanan_admin.php">Layanan</a>
      <a href="datapelanggan.php">Data Pelanggan</a>
      <a href="laporan.php">Laporan</a>
    </div>
  </div>
</body>
</html>

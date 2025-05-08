<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "laundry";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Update user data
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $nama = $_POST['edit_nama'];
    $telepon = $_POST['edit_telepon'];

    $stmt = $conn->prepare("UPDATE users SET username=?, phone=? WHERE id=?");
    $stmt->bind_param("ssi", $nama, $telepon, $id);
    $stmt->execute();
    $stmt->close();
}

// Delete orders only (not user)
if (isset($_GET['delete_orders'])) {
    $id = intval($_GET['delete_orders']);
    $conn->query("DELETE FROM orders WHERE user_id = $id");
    header("Location: datapelanggan.php");
    exit;
}

// Fetch data
$query = "SELECT users.id, users.username, users.phone, 
                 COALESCE(MAX(orders.lokasi), 'Havent Make an Order') AS lokasi
          FROM users
          LEFT JOIN orders ON users.id = orders.user_id
          GROUP BY users.id, users.username, users.phone
          ORDER BY users.id ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Pelanggan - DripLess</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 1000px;
      margin: auto;
      background: #e6f2fb;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      height: 340px;
    }

    h1 {
      text-align: center;
      color: #2a3d66;
    }

    .search-bar {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }

    .search-bar input {
      width: 60%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 20px;
    }

    th, td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #cde2f7;
    }

    .action-btn {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin: 0 4px;
      font-weight: bold;
    }

    .edit-btn {
      background-color: #ffc107;
      color: white;
    }

    .invoice-btn {
      background-color: #17a2b8;
      color: white;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: #fff;
      padding: 25px;
      border-radius: 10px;
      width: 400px;
      text-align: left;
    }

    .modal-content input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .save-btn {
      background-color: #28a745;
      color: white;
    }

    .cancel-btn {
      background-color: #6c757d;
      color: white;
    }

    .back-btn {
      width: 70px;
      height: 30px;
      background-color: #a4cbee;
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      padding-top: 7px;
      text-align: center;
      text-decoration: none;
      font-weight: bold;
      float: right;
      margin-top: 2px;
    }

    .back-btn:hover {
      background-color: #90b8dd;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Data Pelanggan</h1>

    <div class="search-bar">
      <input type="text" id="searchInput" onkeyup="searchCustomer()" placeholder="Cari nama, nomor telepon, atau lainnya...">
    </div>

    <table id="customerTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Telepon</th>
          <th>Alamat</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr data-id="<?= $row['id']; ?>">
                    <td>PL<?= str_pad($row['id'], 3, "0", STR_PAD_LEFT); ?></td>
                    <td class="nama"><?= htmlspecialchars($row['username']); ?></td>
                    <td class="telepon"><?= htmlspecialchars($row['phone']); ?></td>
                    <td class="alamat"><?= htmlspecialchars($row['lokasi']); ?></td>
                    <td>
                        <button class="action-btn edit-btn" onclick="openModal(this)">Edit</button>
                        <button class="action-btn invoice-btn" onclick="location.href='?delete_orders=<?= $row['id']; ?>'">Hapus Pesanan</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">Belum ada pelanggan</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <a href="homepage_admin.php" class="back-btn">Back</a>
  </div>

  <!-- Modal Edit -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <h3>Edit Data Pelanggan</h3>
      <form method="POST">
        <input type="hidden" id="editId" name="edit_id">
        <input type="text" id="editNama" name="edit_nama" placeholder="Nama" required>
        <input type="text" id="editTelepon" name="edit_telepon" placeholder="Nomor Telepon" required>
        <button type="submit" class="action-btn save-btn">Simpan</button>
        <button type="button" class="action-btn cancel-btn" onclick="closeModal()">Batal</button>
      </form>
    </div>
  </div>

  <script>
    let currentRow = null;

    function openModal(button) {
      currentRow = button.closest('tr');
      const id = currentRow.getAttribute('data-id');
      const nama = currentRow.querySelector('.nama').innerText;
      const telepon = currentRow.querySelector('.telepon').innerText;

      document.getElementById('editId').value = id;
      document.getElementById('editNama').value = nama;
      document.getElementById('editTelepon').value = telepon;
      document.getElementById('editModal').style.display = 'flex';
    }

    function closeModal() {
      document.getElementById('editModal').style.display = 'none';
    }

    function searchCustomer() {
      const input = document.getElementById('searchInput').value.toLowerCase();
      const rows = document.querySelectorAll('#customerTable tbody tr');
      rows.forEach(row => {
        const nama = row.querySelector('.nama').innerText.toLowerCase();
        const telepon = row.querySelector('.telepon').innerText.toLowerCase();
        const alamat = row.querySelector('.alamat').innerText.toLowerCase();
        row.style.display = (nama.includes(input) || telepon.includes(input) || alamat.includes(input)) ? '' : 'none';
      });
    }
  </script>
</body>
</html>


<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laundry";  // Nama database

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Menangani penambahan data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addLayanan'])) {
    $jenis_layanan = $_POST['newNama'];
    $harga_per_kg = $_POST['newHarga'];

    if (!empty($jenis_layanan) && !empty($harga_per_kg)) {
        $query = "INSERT INTO layanan (jenis_layanan, harga_per_kg) VALUES ('$jenis_layanan', '$harga_per_kg')";
        mysqli_query($conn, $query);
    }
}

// Menangani pengeditan data
if (
  $_SERVER['REQUEST_METHOD'] == 'POST' &&
  isset($_POST['editId'], $_POST['editNama'], $_POST['editHarga'])
) {
  $id = $_POST['editId'];
  $jenis_layanan = $_POST['editNama'];
  $harga_per_kg = $_POST['editHarga'];

  if (!empty($jenis_layanan) && !empty($harga_per_kg)) {
      $query = "UPDATE layanan SET jenis_layanan = '$jenis_layanan', harga_per_kg = '$harga_per_kg' WHERE id = $id";
      mysqli_query($conn, $query);
  }
}


// Menangani penghapusan data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteId'])) {
    $id = $_POST['deleteId'];
    $query = "DELETE FROM layanan WHERE id = $id";
    mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Jenis Layanan - DripLess</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-image: url('background.png');
      background-size: cover;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 800px;
      background-color: #e6f2fb;
      margin: 50px auto;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 25px;
    }

    .logo {
      width: 170px;
      height: 70px;
    }

    h1 {
      color: #2a3d66;
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
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      margin: 0 4px;
    }

    .edit-btn {
      background-color: #ffc107;
      color: white;
    }

    .delete-btn {
      background-color: #dc3545;
      color: white;
    }

    .add-form {
      background-color: #ffffff;
      padding: 20px;
      border-radius: 10px;
    }

    .add-form input {
      width: 100%;
      padding: 10px;
      margin-top: 8px;
      margin-bottom: 16px;
      border: 1px solid #aacbeb;
      border-radius: 8px;
    }

    .add-btn {
      background-color: #28a745;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    .add-btn:hover {
      background-color: #218838;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: #fff;
      padding: 25px;
      border-radius: 10px;
      width: 400px;
      text-align: center;
    }

    .modal-content input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .modal-content button {
      padding: 10px 15px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin: 5px;
      font-weight: bold;
    }

    .save-btn {
      background-color: #007bff;
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
    <div class="header">
      <img src="gambar/logo.png" alt="DripLess Logo" class="logo">
      <h1>Kelola Jenis Layanan Laundry</h1>
    </div>

    <table id="layananTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Jenis Layanan</th>
          <th>Harga/kg</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Ambil data layanan dari database
        $query = "SELECT * FROM layanan";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>";
          echo "<td>" . $row['id'] . "</td>";
          echo "<td class='nama'>" . $row['jenis_layanan'] . "</td>";
          echo "<td class='harga'>" . $row['harga_per_kg'] . "</td>";
          echo "<td>
                  <button type=\"button\" class=\"action-btn edit-btn\" onclick=\"openModal(this)\">Edit</button>
                  <form method='post' style='display:inline;'>
                    <input type='hidden' name='deleteId' value='" . $row['id'] . "'>
                    <button class='action-btn delete-btn'>Hapus</button>
                  </form>
                </td>";
          echo "</tr>";
        }
        ?>
      </tbody>
    </table>

    <div class="add-form">
      <h3>Tambah Jenis Layanan</h3>
      <form method="post">
        <input type="text" name="newNama" placeholder="Nama Layanan" required>
        <input type="number" name="newHarga" placeholder="Harga per kg (Rp)" required>
        <button type="submit" name="addLayanan" class="add-btn">Tambah</button>
        <a href="homepage_admin.php" class="back-btn">Back</a>
      </form>
    </div>
  </div>

  <!-- Modal Edit -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <h3>Edit Layanan</h3>
      <form method="post">
        <input type="text" name="editNama" placeholder="Nama Layanan" required>
        <input type="number" name="editHarga" placeholder="Harga per kg" required>
        <input type="hidden" name="editId" value="">
        <button class="save-btn" type="submit">Simpan</button>
      </form>
    </div>
    <button class="cancel-btn" onclick="closeModal()">Batal</button>
  </div>

  <script>
    // Function for editing modal
    function openModal(button) {
      const row = button.closest('tr');
      const nama = row.querySelector('.nama').innerText;
      const harga = row.querySelector('.harga').innerText;
      const form = document.querySelector('#editModal form');
      form.querySelector('input[name="editNama"]').value = nama;
      form.querySelector('input[name="editHarga"]').value = harga;
      form.querySelector('input[name="editId"]').value = row.cells[0].innerText;
      document.getElementById("editModal").style.display = "flex";
    }

    function closeModal() {
      document.getElementById("editModal").style.display = "none";
    }
  </script>
</body>
</html>

<?php
// Tutup koneksi setelah selesai
mysqli_close($conn);
?>

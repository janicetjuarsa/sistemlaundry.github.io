<?php
session_start();

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "laundry");

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data layanan dari database
$query = "SELECT jenis_layanan FROM layanan";
$result = $conn->query($query);

// Memeriksa apakah ada data layanan
if ($result->num_rows > 0) {
    $layananOptions = '';
    while ($row = $result->fetch_assoc()) {
        $layananOptions .= "<option value='" . htmlspecialchars($row['jenis_layanan']) . "'>" . htmlspecialchars($row['jenis_layanan']) . "</option>";
    }
} else {
    $layananOptions = "<option value=''>Tidak ada layanan tersedia</option>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pesan Layanan - DripLess</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-size: cover;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      background-color: #e6f2fb;
      margin: 40px auto;
      padding: 20px 40px;
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    h1 {
      text-align: center;
      color: #2a3d66;
      margin-top: 10px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #aacbeb;
      border-radius: 8px;
      margin-top: 5px;
      box-sizing: border-box;
    }

    button, .back-btn {
      width: 100%;
      background-color: #a4cbee;
      color: white;
      padding: 12px;
      margin-top: 25px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 16px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-weight: bold;
    }

    button:hover, .back-btn:hover {
      background-color: #90b8dd;
    }

    .order-details p {
      background-color: #fff;
      padding: 10px 15px;
      border-radius: 8px;
      margin: 8px 0;
    }

    #confirmation {
      display: none;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">

    <!-- FORM -->
    <div id="form-section">
      <h1>Form Pemesanan Laundry</h1>
      <form id="orderForm">
        <label for="layanan">Jenis Layanan</label>
        <select id="layanan" name="layanan" required>
          <option value="">-- Pilih Layanan --</option>
          <?php echo $layananOptions; // Menampilkan opsi layanan dari database ?>
        </select>

        <label for="berat">Estimasi Berat (kg)</label>
        <input type="number" id="berat" name="berat" min="1" required placeholder="Angka harus bilangan bulat">

        <label for="lokasi">Lokasi Penjemputan</label>
        <input type="text" id="lokasi" name="lokasi" required>

        <label for="waktu">Waktu Penjemputan</label>
        <input type="datetime-local" id="waktu" name="waktu" required>

        <button type="submit">Kirim Pesanan</button>
      </form>
    </div>

    <!-- KONFIRMASI -->
    <div id="confirmation">
      <h1>Pesanan Berhasil Dibuat!</h1>
      <p class="success-msg">Terima kasih telah menggunakan layanan DripLess.</p>
      <div class="order-details" id="orderDetails"></div>
      <a href="homepage.php" class="back-btn">Kembali ke Dashboard</a>
    </div>
  </div>

  <script>
    const form = document.getElementById('orderForm');
    const formSection = document.getElementById('form-section');
    const confirmation = document.getElementById('confirmation');
    const orderDetails = document.getElementById('orderDetails');

    form.addEventListener('submit', async function(e) {
      e.preventDefault();
    
      const layanan = document.getElementById('layanan').value;
      const berat = document.getElementById('berat').value;
      const lokasi = document.getElementById('lokasi').value;
      const waktu = document.getElementById('waktu').value;
    
      const waktuFormatted = new Date(waktu).toLocaleString('id-ID', {
        dateStyle: 'long', timeStyle: 'short'
      });
    
      const response = await fetch('submit_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `layanan=${encodeURIComponent(layanan)}&berat=${berat}&lokasi=${encodeURIComponent(lokasi)}&waktu=${encodeURIComponent(waktu)}`
      });
    
      const result = await response.json();
    
      if (result.success) {
        orderDetails.innerHTML = `
          <p><strong>Jenis Layanan:</strong> ${layanan}</p>
          <p><strong>Berat:</strong> ${berat} kg</p>
          <p><strong>Lokasi Penjemputan:</strong> ${lokasi}</p>
          <p><strong>Waktu Penjemputan:</strong> ${waktuFormatted}</p>
          <p><strong>Status:</strong> Menunggu Penjemputan</p>
        `;
        formSection.style.display = 'none';
        confirmation.style.display = 'block';
      } else {
        alert(result.message || "Terjadi kesalahan saat menyimpan data.");
      }
    });
  </script>
</body>
</html>

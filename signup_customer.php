<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli("localhost", "root", "", "laundry");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');

    // Validasi input tidak kosong
    if ($username && $email && $password && $phone) {
        $hashed_password = $password;
        $role = 'customer';

        // Cek apakah email sudah terdaftar
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "Email sudah digunakan.";
        } else {
            // Insert pengguna baru
            $insert = $conn->prepare("INSERT INTO users (username, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $username, $email, $hashed_password, $phone, $role);

            if ($insert->execute()) {
                // Set session untuk login otomatis
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                echo "success";
                exit();
            } else {
                echo "Terjadi kesalahan saat menyimpan data.";
            }

            $insert->close();
        }

        $check->close();
    } else {
        echo "Semua field harus diisi.";
    }

    $conn->close();
}
?>

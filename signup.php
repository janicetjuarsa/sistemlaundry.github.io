<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');

    if ($username && $email && $password && $phone) {
        $hashed_password = $password
        $role = 'customer';

        // Cek apakah email sudah terdaftar
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email sudah digunakan.'); window.location.href='signup_customer.html';</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $role);

            if ($stmt->execute()) {
                header("Location: login.html");
                exit();
            } else {
                echo "<script>alert('Terjadi kesalahan saat mendaftar.');</script>";
            }

            $stmt->close();
        }

        $check->close();
    } else {
        echo "<script>alert('Semua field harus diisi.'); window.location.href='signup_customer.html';</script>";
    }

    $conn->close();
}
?>

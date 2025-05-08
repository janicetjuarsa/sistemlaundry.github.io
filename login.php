<?php
session_start();
$conn = new mysqli("localhost", "root", "", "laundry");

// Aktifkan error reporting untuk debugging (nonaktifkan di production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Simpan session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Arahkan ke dashboard sesuai role
                switch ($user['role']) {
                    case 'customer':
                        header("Location: homepage.html");
                        break;
                    case 'owner':
                        header("Location: login_owner.php");
                        break;
                    default:
                        echo "<script>alert('Unknown role.'); window.location.href='login.html';</script>";
                        break;
                }
                exit();
            } else {
                echo "<script>alert('Incorrect password.'); window.location.href='login.html';</script>";
                exit();
            }
        } else {
            echo "<script>alert('No user found with that email.'); window.location.href='login.html';</script>";
            exit();
        }

        $stmt->close();
    }
}

$conn->close();
?>

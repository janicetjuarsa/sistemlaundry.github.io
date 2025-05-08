<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "laundry");

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connection failed.']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $layanan = $_POST['layanan'];
    $berat = $_POST['berat'];
    $lokasi = $_POST['lokasi'];
    $waktu = $_POST['waktu'];
    $status = "Menunggu Penjemputan";

    $stmt = $conn->prepare("INSERT INTO orders (user_id, layanan, berat, lokasi, status, waktu_penjemputan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdsss", $user_id, $layanan, $berat, $lokasi, $status, $waktu);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save order.']);
    }

    $stmt->close();
    $conn->close();
}
?>

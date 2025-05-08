CREATE DATABASE laundry;
USE laundry;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'owner', 'admin') NOT NULL
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    layanan VARCHAR(50) NOT NULL,
    berat DECIMAL(5,2) NOT NULL,
    lokasi TEXT NOT NULL,
    waktu_penjemputan DATETIME NOT NULL,
    status VARCHAR(50) DEFAULT 'Menunggu Pengambilan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jenis_layanan VARCHAR(255) NOT NULL,
    harga_per_kg INT NOT NULL
);







<?php
$host = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$database = "pet_clinic";

// Connect to MySQL server first (without database)
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql_db = "CREATE DATABASE IF NOT EXISTS $database";
if (!$conn->query($sql_db)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Create necessary tables if they do not exist
$tables = [
    "CREATE TABLE IF NOT EXISTS vaccines (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_type VARCHAR(50),
        vaccine_name VARCHAR(100),
        age_range VARCHAR(50),
        description TEXT,
        quantity INT DEFAULT 0
    )",
    "CREATE TABLE IF NOT EXISTS pets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_name VARCHAR(100),
        email VARCHAR(100),
        pet_name VARCHAR(100),
        pet_type VARCHAR(50),
        breed VARCHAR(100),
        age VARCHAR(50),
        notes TEXT
    )",
    "CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_id INT,
        owner_name VARCHAR(100),
        email VARCHAR(100),
        pet_name VARCHAR(100),
        appointment_date DATE,
        appointment_time TIME,
        service_type VARCHAR(100),
        reason TEXT,
        phone VARCHAR(20),
        status VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS adoption_pets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT DEFAULT NULL,
        pet_name VARCHAR(100),
        pet_type VARCHAR(50),
        age VARCHAR(50),
        description TEXT,
        image_path VARCHAR(255)
    )",
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255),
        role VARCHAR(20) DEFAULT 'Pet Owner',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $query) {
    if (!$conn->query($query)) {
        die("Error creating table: " . $conn->error);
    }
}

// Ensure 'role' column exists in case the table was created previously without it
$checkRole = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($checkRole->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'Pet Owner'");
}

// Ensure 'image_path' column exists in pets table
$checkPetImage = $conn->query("SHOW COLUMNS FROM pets LIKE 'image_path'");
if ($checkPetImage->num_rows == 0) {
    $conn->query("ALTER TABLE pets ADD COLUMN image_path VARCHAR(255) DEFAULT NULL");
}

// Ensure 'age' column in pets is VARCHAR to allow units like 'Months'
$checkAgeType = $conn->query("SHOW COLUMNS FROM pets LIKE 'age'");
if ($checkAgeType && $checkAgeType->num_rows > 0) {
    $row = $checkAgeType->fetch_assoc();
    if (strpos(strtolower($row['Type']), 'int') !== false) {
        $conn->query("ALTER TABLE pets MODIFY COLUMN age VARCHAR(50)");
    }
}

// Ensure 'owner_id' column exists in adoption_pets table
$checkOwner = $conn->query("SHOW COLUMNS FROM adoption_pets LIKE 'owner_id'");
if ($checkOwner && $checkOwner->num_rows == 0) {
    $conn->query("ALTER TABLE adoption_pets ADD COLUMN owner_id INT DEFAULT NULL AFTER id");
}
?>

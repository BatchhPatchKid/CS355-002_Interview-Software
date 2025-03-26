<?php
// Database connection settings
$host = 'localhost';
$user = 'root';
$password = ''; // no password for now
$dbname = 'databaseCS355';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define encryption key
$encryptionKey = '';

// Process account creation if POST data is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve input values
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']); // Expect 'instructor' or 'ta'

    // Check required fields
    if (empty($username) || empty($password) || empty($role)) {
        die("Missing required fields.");
    }

    // Prepare the SQL INSERT statement using AES_ENCRYPT for the password column
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, AES_ENCRYPT(?, ?), ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssss", $username, $password, $encryptionKey, $role);

    if ($stmt->execute()) {
        echo "Account created successfully.";
    } else {
        echo "Error creating account: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
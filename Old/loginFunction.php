<?php
session_start();

// Database connection settings
$host = 'localhost';
$user = 'user';
$password = 'pass';
$dbname = 'databaseUser';

// Create connection using mysqli
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define encryption key
$encryptionKey = '';

// Process login if POST data is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve input values
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check required fields
    if (empty($username) || empty($password)) {
        die("Missing username or password.");
    }

    // Prepare the SQL SELECT statement that decrypts the stored password
    $stmt = $conn->prepare("SELECT user_id, username, AES_DECRYPT(password, ?) AS decrypted_password, role FROM users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $encryptionKey, $username);
    $stmt->execute();
    
    // Bind the results
    $stmt->bind_result($user_id, $db_username, $decrypted_password, $role);
    
    if ($stmt->fetch()) {
        // Compare the decrypted password with the provided password
        if (trim($decrypted_password) === $password) {
            // Successful login; create session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $db_username;
            $_SESSION['role'] = $role;
            echo "Login successful.";
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "Invalid username or password.";
    }
    $stmt->close();
}

$conn->close();
?>

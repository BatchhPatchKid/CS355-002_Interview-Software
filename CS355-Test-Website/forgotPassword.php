<?php 
require_once 'styleColor.php';
?>


<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main">
        <h1>Reset Your Password</h1>
        <form action="forgotPassword.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your Username" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter your new Password" required>

            <div class="wrap">
                <button type="submit">Reset Password</button>
            </div>
            <button onclick="location.href='login.php'">Back to Login</button>
        </form>
    </div>
</body>
</html>

<?php

// Enable strict error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection settings
$host   = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'databaseCS355';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create a connection to the database
        $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
        $conn->set_charset("utf8");

        // Retrieve and sanitize input values
        $username = trim($_POST['username']);
        $new_password = trim($_POST['new_password']);

        // Validate input
        if (empty($username) || empty($new_password)) {
            throw new Exception("Username or new password not provided.");
        }

        // Check if the username exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // If user exists, proceed with password update
        if ($result->num_rows === 1) {
            // Hash the new password
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the user's password
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $updateStmt->bind_param("ss", $hashedPassword, $username);
            $updateStmt->execute();

            echo "Password successfully updated. <a href='login.php'>Click here to login</a>";
            $updateStmt->close();
        } else {
            echo "No user found with the provided username.";
        }

        $stmt->close();
        $conn->close();
    } catch (mysqli_sql_exception $e) {
        echo "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

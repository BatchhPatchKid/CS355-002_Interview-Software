<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main">
        <h1>Create Account</h1>
        <form action="createAccount.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your Username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your Password" required>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="instructor">Instructor</option>
                <option value="ta">TA</option>
            </select>

            <div class="wrap">
                <button type="submit">Create Account</button>
            </div>
            <button onclick="location.href='mainscreen.php'">Back</button>
        </form>
    </div>
</body>
</html>
<?php

session_start();
require_once 'styleColor.php';
// Process account creation if POST data is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection settings
    $host   = 'localhost';
    $dbUser = 'root';
    $dbPass = ''; // no password for now
    $dbName = 'databaseCS355';

    try {
        // Create the connection
        $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
        $conn->set_charset("utf8");

        // Retrieve and sanitize input values
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $role     = trim($_POST['role']); // Expect 'instructor' or 'ta'

        // Check required fields
        if (empty($username) || empty($password) || empty($role)) {
            throw new Exception("Missing required fields.");
        }

        // Hash the password using PHP's password_hash function
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL INSERT statement
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $role);

        // Execute the statement
        $stmt->execute();
        echo "Account created successfully.";

        $stmt->close();
        $conn->close();
    } catch (mysqli_sql_exception $e) {
        // Duplicate entry error code is 1062
        if ($e->getCode() == 1062) {
            echo "Error: The username already exists. Please choose a different username.";
        } else {
            echo "Error creating account: " . $e->getMessage();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

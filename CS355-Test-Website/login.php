<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main">
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your Username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your Password" required>

            <div class="wrap">
                <button type="submit">Login</button>
            </div>
            <button onclick="location.href='mainscreen.php'">Back</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();

// Enable strict error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection settings
$host   = 'localhost';
$dbUser = 'root';
$dbPass = ''; // no password for now
$dbName = 'databaseCS355';

try {
    // Create a connection to the database
    $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
    $conn->set_charset("utf8");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve and sanitize input values
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Validate input
        if (empty($username) || empty($password)) {
            throw new Exception("Missing required fields.");
        }

        // Prepare a SQL SELECT statement to retrieve the user by username
        $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user was found
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Verify the password using password_verify
            if (password_verify($password, $row['password'])) {
                // Set session variables upon successful login
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $row['role'];  // role is still stored if needed later

                // Redirect to a protected page (for example, addQuestion.php)
                header("Location: mainscreen.php");
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with the provided username.";
        }
        $stmt->close();
    }
    $conn->close();
} catch (mysqli_sql_exception $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
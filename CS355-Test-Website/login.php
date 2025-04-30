<?php
ob_start();
require_once 'styleColor.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host   = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'databaseCS355';

try {
    $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
    $conn->set_charset("utf8");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($password)) {
            throw new Exception("Missing required fields.");
        }

        $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $row['role'];

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

            <!-- FIXED: This button is now type="button" to prevent form submission -->
            <button type="button" onclick="location.href='mainscreen.php'">Back</button>

            <p>
                <a href="forgotPassword.php">Forgot Password?</a>
            </p>
        </form>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>
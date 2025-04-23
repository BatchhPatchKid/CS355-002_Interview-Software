<?php
require_once 'styleColor.php';
session_start();

// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Enable strict MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$successMsg = "";
$errorMsg   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection settings
    $host   = 'localhost';
    $dbUser = 'root';
    $dbPass = ''; // No password for now
    $dbName = 'databaseCS355';

    try {
        // Create a connection to the database
        $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
        $conn->set_charset("utf8");

        // Retrieve and sanitize input values
        $class      = trim($_POST['class']);
        $competency = trim($_POST['competency']);
        $subject    = trim($_POST['subject']);

        // Validate required fields
        if (empty($class) || empty($competency) || empty($subject)) {
            throw new Exception("All fields are required.");
        }

        // Prepare a SQL INSERT statement for the class_competency table
        $stmt = $conn->prepare("INSERT INTO class_competency (class_name, competency_name, class_subject) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $class, $competency, $subject);
        $stmt->execute();

        $successMsg = "Class and Competency added successfully.";
        $stmt->close();
        $conn->close();
    } catch (mysqli_sql_exception $e) {
        $errorMsg = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $errorMsg = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Class and Competency</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Display any error or success messages -->
    <?php if (!empty($errorMsg)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($errorMsg); ?></p>
    <?php endif; ?>
    <?php if (!empty($successMsg)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($successMsg); ?></p>
    <?php endif; ?>

    <div class="add-question">
        <div class="form-container">
            <h1>Add Class and Competency</h1>
            <form action="addClass.php" method="POST">
                <label for="class">Class Name:</label>
                <input type="text" id="class" name="class" placeholder="Enter class name" required>
                
                <label for="competency">Competency Name:</label>
                <input type="text" id="competency" name="competency" placeholder="Enter competency name" required>

                <label for="subject">Competency Category:</label>
                <input type="text" id="subject" name="subject" placeholder="Enter category name" required>
                
                <div class="button-container">
                    <button class="submit-button" type="submit">Submit</button>
                    <button class="back-button" type="button" onclick="location.href='mainscreen.php';">Back</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
session_start();

// Database connection settings
$host = 'localhost';
$dbUser = 'root';
$dbPass = ''; // no password for now
$dbName = 'databaseCS355';

// Create connection to the database
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input values
    $class_name = trim($_POST['class']);
    $competency_name = trim($_POST['competency']);
    $class_subject = trim($_POST['subject']);
    
    // Simple validation: require all three fields
    if (empty($class_name) || empty($competency_name) || empty($class_subject)) {
        die("Class name, competency name, and subject are required.");
    }
    
    // Prepare an SQL INSERT statement with the new column
    $stmt = $conn->prepare("INSERT INTO class_competency (class_name, competency_name, class_subject) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters: three strings ("sss")
    $stmt->bind_param("sss", $class_name, $competency_name, $class_subject);
    
    // Try executing the statement and catch any duplicate entry error
    try {
        if ($stmt->execute()) {
            header("Location: addClass.html");
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo "Error: Duplicate entry. This class, competency, and subject combination already exists.";
        } else {
            echo "Error inserting record: " . $e->getMessage();
        }
    }
    
    $stmt->close();
}

$conn->close();
?>
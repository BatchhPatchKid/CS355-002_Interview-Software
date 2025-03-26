<?php
session_start();

// Database connection settings
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

// Create connection using mysqli
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use the session user_id if available, default to 1 for testing
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    
    // Retrieve and sanitize input values
    $question_text   = trim($_POST['question']);
    $class_name      = trim($_POST['class']);
    $competency_name = trim($_POST['competency']);
    // Using the category field as notes; adjust as needed
    $question_notes  = trim($_POST['category']);
    
    // Optionally validate inputs here
    
    // Prepare an SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO logged_questions (user_id, class_name, competency_name, question_text, question_notes, date_added) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters to the SQL statement: "issss" means integer, string, string, string, string.
    $stmt->bind_param("issss", $user_id, $class_name, $competency_name, $question_text, $question_notes);
    
    // Execute the statement and check for success
    if ($stmt->execute()) {
        echo "Question added successfully!";
    } else {
        echo "Error inserting question: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>
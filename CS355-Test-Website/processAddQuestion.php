<?php
session_start();
require_once 'generateQuestionID.php';

$host   = 'localhost';
$dbUser = 'root';
$dbPass = ''; // No password for now
$dbName = 'databaseCS355';

// Create connection to the database
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use session user_id if available, default to 1 for testing
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    
    // Retrieve and sanitize input values
    $question_text    = trim($_POST['question']);
    $class_name       = trim($_POST['class']);
    $competency_name  = trim($_POST['competency']);
    $class_subject    = trim($_POST['subject']);
    $question_notes   = trim($_POST['notes']);
    $difficulty       = trim($_POST['difficulty']);
    
    // Generate the custom question ID using your function
    try {
        $custom_id = generate_question_id($difficulty, $conn);
    } catch (Exception $e) {
        die("Error generating ID: " . $e->getMessage());
    }
    
    // Prepare an SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO logged_questions (logged_question_id, user_id, class_name, competency_name, class_subject, question_text, question_notes, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters:
    // "sisssss" means: string for custom_id, integer for user_id, then four strings for class_name, competency_name, class_subject, question_text, and one string for question_notes.
    $stmt->bind_param("sisssss", $custom_id, $user_id, $class_name, $competency_name, $class_subject, $question_text, $question_notes);
    
    // Execute the statement; on success, redirect back to addQuestion.php
    if ($stmt->execute()) {
        header("Location: addQuestion.php");
        exit();
    } else {
        echo "Error inserting question: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>
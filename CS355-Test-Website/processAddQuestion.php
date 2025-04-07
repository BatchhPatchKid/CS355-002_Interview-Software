<?php
session_start();

// Only process if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Redirect to login.php if the user is not logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $host   = 'localhost';
    $dbUser = 'root';
    $dbPass = ''; // No password for now
    $dbName = 'databaseCS355';

    // Create connection
    $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    // Retrieve the currently logged-in user's ID from session
    $user_id = $_SESSION['user_id'];

    // Retrieve and sanitize the form input values
    $question_text   = trim($_POST['question']);
    $class_name      = trim($_POST['class']);
    $competency_name = trim($_POST['competency']);
    $class_subject   = trim($_POST['subject']);
    $question_notes  = trim($_POST['notes']);

    // Prepare the SQL INSERT statement (note: no difficulty column included)
    $stmt = $conn->prepare("INSERT INTO logged_questions (user_id, class_name, competency_name, class_subject, question_text, question_notes, date_added) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters:
    // "i" for user_id, followed by 5 strings for the other fields.
    $stmt->bind_param("isssss", $user_id, $class_name, $competency_name, $class_subject, $question_text, $question_notes);

    if ($stmt->execute()) {
        header("Location: mainscreen.php");
        exit();
    } else {
        echo "Error inserting question: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // If not a POST request, redirect back to the form page
    header("Location: addQuestion.php");
    exit();
}
?>
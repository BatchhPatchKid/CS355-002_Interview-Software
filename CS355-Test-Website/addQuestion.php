<?php
session_start();
require_once 'generateQuestionID.php';

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

    $question_text       = trim($_POST['question']);
    $class_name          = trim($_POST['class']);
    $competency_name     = trim($_POST['competency']);
    $competency_subject  = trim($_POST['category']);
    $question_notes      = "";
    $difficulty = intval($_POST['difficulty']); // Example: <input type="number" name="difficulty">

    // Generate the custom question ID
    try {
        $custom_id = generate_question_id($difficulty, $conn);
    } catch (Exception $e) {
        die("Error generating ID: " . $e->getMessage());
    }

    // Modify your query to insert the custom ID
    $stmt = $conn->prepare("INSERT INTO logged_questions (logged_question_id, user_id, class_name, competency_name, competency_subject, question_text, question_notes, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iisssss", $custom_id, $user_id, $class_name, $competency_name, $competency_subject, $question_text, $question_notes);

    if ($stmt->execute()) {
        header("Location: add question.html");
        exit();
    } else {
        echo "Error inserting question: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
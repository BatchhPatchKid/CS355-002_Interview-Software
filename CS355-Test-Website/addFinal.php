<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $host = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'databaseCS355';

    $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    $logged_id = $_POST['id'];

    $stmt = $conn->prepare("INSERT INTO competency_questions (
        user_id, class_name, competency_name, class_subject,
        question_text, question_notes, question_id, parent_id, date_added
    )
    SELECT user_id, class_name, competency_name, class_subject,
           question_text, question_notes, logged_question_id, parent_id, NOW()
    FROM logged_questions
    WHERE logged_question_id = ?");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $logged_id);

    if ($stmt->execute()) {
        // Delete the original from logged_questions
        $deleteStmt = $conn->prepare("DELETE FROM logged_questions WHERE logged_question_id = ?");
        $deleteStmt->bind_param("s", $logged_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        echo "Question successfully added to competency questions.";
    } else {
        echo "Error promoting question: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
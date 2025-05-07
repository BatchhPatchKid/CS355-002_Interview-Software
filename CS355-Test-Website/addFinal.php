<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Must be logged in
    if (!isset($_SESSION['user_id'])) {
        echo "Invalid request.";
        exit;
    }

    $host   = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'databaseCS355';

    $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    // Check that the current user is an instructor
    $sessionUserId = $_SESSION['user_id'];
    $roleStmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    if (!$roleStmt) {
        die("Prepare failed: " . $conn->error);
    }
    $roleStmt->bind_param("i", $sessionUserId);
    $roleStmt->execute();
    $roleStmt->bind_result($role);
    if (!$roleStmt->fetch() || $role !== 'instructor') {
        echo "Error: only instructors can add to the competency_questions table";
        $roleStmt->close();
        $conn->close();
        exit;
    }
    $roleStmt->close();

    // Now safe to promote
    $logged_id = $_POST['id'];

    $stmt = $conn->prepare(
        "INSERT INTO competency_questions (
            user_id, class_name, competency_name, class_subject,
            question_text, question_notes, question_id, parent_id, date_added
        )
        SELECT user_id, class_name, competency_name, class_subject,
               question_text, question_notes, logged_question_id, parent_id, NOW()
        FROM logged_questions
        WHERE logged_question_id = ?"
    );
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $logged_id);

    if ($stmt->execute()) {
        // Delete from logged_questions
        $deleteStmt = $conn->prepare("DELETE FROM logged_questions WHERE logged_question_id = ?");
        $deleteStmt->bind_param("i", $logged_id);
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
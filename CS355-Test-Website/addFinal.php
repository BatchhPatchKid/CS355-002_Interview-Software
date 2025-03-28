<?php
session_start();

// Database connection settings
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $questionId = intval($_POST['id']);

    
    $checkSql = "SELECT COUNT(*) FROM competency_questions WHERE question_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $questionId);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo "This question has already been added.";
        exit; 
    }

    
    $sql = "SELECT question_text, user_id, class_name, competency_name, question_notes, date_added 
            FROM logged_questions WHERE logged_question_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        
        $insertSql = "INSERT INTO competency_questions (question_id, question_text, user_id, class_name, competency_name, question_notes, date_added) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param(
            "isissss",
            $questionId, 
            $row['question_text'],
            $row['user_id'],
            $row['class_name'],
            $row['competency_name'],
            $row['question_notes'],
            $row['date_added']
        );

        if ($insertStmt->execute()) {
            
            $deleteSql = "DELETE FROM logged_questions WHERE logged_question_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $questionId);

            if ($deleteStmt->execute()) {
                echo "Question successfully added to Competency Questions and removed from Logged Questions!";
            } else {
                echo "Error deleting question from Logged Questions: " . $deleteStmt->error;
            }

            $deleteStmt->close();
        } else {
            echo "Error inserting question: " . $insertStmt->error;
        }

        $insertStmt->close();
    } else {
        echo "Error: Question not found.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
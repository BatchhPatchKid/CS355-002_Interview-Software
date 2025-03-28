<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

// Connect to the database
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $question_id = intval($_POST['id']); 

    
    $stmt = $conn->prepare("DELETE FROM logged_questions WHERE logged_question_id = ?");
    $stmt->bind_param("i", $question_id);
    
    
    if ($stmt->execute()) {
        echo "Question deleted successfully!";
    } else {
        echo "Error deleting question: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<?php
$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'databaseCS355';

// Connect to the database
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && !empty($_POST['id']) 
    && !empty($_POST['table'])
) {
    $question_id = $_POST['id'];
    $table       = $_POST['table'];

    // Whitelist allowed tables
    if ($table === 'logged_questions') {
        $sql = "DELETE FROM logged_questions WHERE logged_question_id = ?";
    }
    elseif ($table === 'competency_questions') {
        $sql = "DELETE FROM competency_questions WHERE question_id = ?";
    }
    else {
        echo "Error: invalid table.";
        exit;
    }

    // Prepare & execute
    if ($stmt = $conn->prepare($sql)) {
        // both IDs are stored as strings (codes), so bind as "s"
        $stmt->bind_param("s", $question_id);
        if ($stmt->execute()) {
            echo "Question deleted successfully!";
        } else {
            echo "Error deleting question: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Prepare failed: " . $conn->error;
    }
}
else {
    echo "Invalid request.";
}

$conn->close();
?>
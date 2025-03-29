<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$result = $conn->query("SELECT * FROM logged_questions ORDER BY RAND() LIMIT 1");

if ($result && $result->num_rows > 0) {
    $question = $result->fetch_assoc();
    echo json_encode($question);
} else {
    echo json_encode(["error" => "No questions found"]);
}

$conn->close();
?>

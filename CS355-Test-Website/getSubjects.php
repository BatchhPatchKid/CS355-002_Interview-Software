<?php
// getSubjects.php

// Database connection
$host   = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'databaseCS355';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8");

if (isset($_GET['class']) && isset($_GET['competency'])) {
    $class = $conn->real_escape_string($_GET['class']);
    $competency = $conn->real_escape_string($_GET['competency']);

    // Query distinct subjects for the selected class and competency
    $query = "SELECT DISTINCT class_subject FROM class_competency WHERE class_name = '$class' AND competency_name = '$competency'";
    $result = $conn->query($query);

    $subjects = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row['class_subject'];
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['subjects' => $subjects]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Class or competency not provided']);
}

$conn->close();
?>

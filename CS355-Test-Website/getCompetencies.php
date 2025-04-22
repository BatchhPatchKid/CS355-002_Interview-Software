<?php

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

if (isset($_GET['class'])) {
    $class = $conn->real_escape_string($_GET['class']);

    // Query distinct competencies for the selected class
    $query = "SELECT DISTINCT competency_name FROM class_competency WHERE class_name = '$class'";
    $result = $conn->query($query);

    $competencies = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $competencies[] = $row['competency_name'];
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['competencies' => $competencies]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No class provided']);
}

$conn->close();
?>

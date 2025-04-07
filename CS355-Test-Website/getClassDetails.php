<?php
header('Content-Type: application/json');

if (!isset($_GET['class']) || empty($_GET['class'])) {
    echo json_encode(["error" => "Missing class parameter."]);
    exit();
}

$class = $_GET['class'];

$host   = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'databaseCS355';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}
$conn->set_charset("utf8");

// Query for competencies
$stmt = $conn->prepare("SELECT DISTINCT competency_name FROM class_competency WHERE class_name = ?");
$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();
$competencies = [];
while ($row = $result->fetch_assoc()) {
    $competencies[] = $row['competency_name'];
}
$stmt->close();

// Query for subjects
$stmt = $conn->prepare("SELECT DISTINCT class_subject FROM class_competency WHERE class_name = ?");
$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();
$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row['class_subject'];
}
$stmt->close();

$conn->close();

echo json_encode(["competencies" => $competencies, "subjects" => $subjects]);
?>
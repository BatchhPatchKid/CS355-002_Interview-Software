<?php
// updateQuestion.php
header('Content-Type: application/json; charset=utf-8');
// suppress notices & warnings
error_reporting(E_ERROR | E_PARSE);

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Not authenticated']);
    exit;
}

// whitelist
$allowed = [
    'competency_questions' => 'question_id',
    'logged_questions'     => 'logged_question_id',
];

$table = $_POST['table'] ?? '';
if (!isset($allowed[$table])) {
    echo json_encode(['status'=>'error','message'=>'Invalid table']);
    exit;
}
$idColumn = $allowed[$table];

// fields to update
$fields = [
    'question_text',
    'class_name',
    'competency_name',
    'class_subject',
    'question_notes',
];

$data = [];
foreach ($fields as $f) {
    $data[$f] = trim($_POST[$f] ?? '');
}
$id = intval($_POST['id'] ?? 0);

if ($id <= 0 || $data['question_text'] === '') {
    echo json_encode(['status'=>'error','message'=>'Missing required fields']);
    exit;
}

// build SQL
$setParts = array_map(function($f){ return "`$f` = ?"; }, $fields);
$sql = sprintf(
    "UPDATE `%s` SET %s WHERE `%s` = ?",
    $table,
    implode(', ', $setParts),
    $idColumn
);

$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'databaseCS355';
$conn     = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status'=>'error','message'=>'DB connection failed']);
    exit;
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status'=>'error','message'=>'Prepare failed: '.$conn->error]);
    exit;
}

// bind: 5 strings + 1 integer
$types  = str_repeat('s', count($fields)) . 'i';
$params = array_merge(array_values($data), [$id]);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>'Execute failed: '.$stmt->error]);
}

$stmt->close();
$conn->close();
exit;
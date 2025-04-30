<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
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

    $user_id        = $_SESSION['user_id'];
    $question_text  = trim($_POST['question']);
    $class_name     = trim($_POST['class']);
    $competency_name = trim($_POST['competency']);
    $class_subject  = trim($_POST['subject']);
    $question_notes = trim($_POST['notes']);
    $difficulty     = intval($_POST['difficulty']);
    $parent_id      = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

    function generate_base_question_id($difficulty, $conn) {
        $maxNN = null;

        $query = "
            SELECT MAX(SUBSTRING(id, 2, 2)) AS max_nn FROM (
                SELECT logged_question_id AS id FROM logged_questions
                UNION ALL
                SELECT question_id AS id FROM competency_questions
            ) AS combined
            WHERE LEFT(id, 1) = ? AND RIGHT(id, 2) = '00'
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $difficulty);
        $stmt->execute();
        $stmt->bind_result($maxNN);
        $stmt->fetch();
        $stmt->close();

        $nextNN = $maxNN !== null ? intval($maxNN) + 1 : 1;
        return sprintf('%d%02d00', $difficulty, $nextNN);
    }

    function generate_followup_question_id_from_parent($parent_id, $conn, $difficulty) {
        $nn = substr($parent_id, 1, 2);
        $maxFF = null;

        $query = "
            SELECT MAX(RIGHT(id, 2)) AS max_ff FROM (
                SELECT logged_question_id AS id FROM logged_questions
                UNION ALL
                SELECT question_id AS id FROM competency_questions
            ) AS combined
            WHERE SUBSTRING(id, 2, 2) = ? AND RIGHT(id, 2) <> '00'
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $nn);
        $stmt->execute();
        $stmt->bind_result($maxFF);
        $stmt->fetch();
        $stmt->close();

        $nextFF = $maxFF !== null ? intval($maxFF) + 1 : 1;
        return sprintf('%d%s%02d', $difficulty, $nn, $nextFF);
    }

    if ($parent_id) {
        $question_id = generate_followup_question_id_from_parent($parent_id, $conn, $difficulty);
    } else {
        $question_id = generate_base_question_id($difficulty, $conn);
    }

    $stmt = $conn->prepare("INSERT INTO logged_questions (
        user_id, class_name, competency_name, class_subject,
        question_text, question_notes, logged_question_id, parent_id, date_added
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("isssssss",
        $user_id, $class_name, $competency_name, $class_subject,
        $question_text, $question_notes, $question_id, $parent_id
    );

    if ($stmt->execute()) {
        $redirectUrl = "addQuestion.php?success=1" .
                       "&question=" . urlencode($question_text) .
                       "&class=" . urlencode($class_name) .
                       "&competency=" . urlencode($competency_name) .
                       "&subject=" . urlencode($class_subject) .
                       "&notes=" . urlencode($question_notes);
        header("Location: $redirectUrl");
        exit();
    } else {
        echo "Error inserting question: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: addQuestion.php");
    exit();
}
?>
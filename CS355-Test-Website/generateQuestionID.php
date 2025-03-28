<?php
function generate_question_id($difficulty, $conn) {
    if ($difficulty < 1 || $difficulty > 4) {
        throw new Exception("Difficulty must be between 1 and 4.");
    }

    $prefix = (string)$difficulty;

    // Initialize variable for IDE clarity
    $max_question_num = null;

    $stmt = $conn->prepare("
        SELECT MAX(SUBSTRING(logged_question_id, 2, 2)) AS max_question_num
        FROM logged_questions
        WHERE logged_question_id LIKE CONCAT(?, '%')
    ");
    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $stmt->bind_result($max_question_num);
    $stmt->fetch();
    $stmt->close();

    $next_question_num = $max_question_num !== null ? intval($max_question_num) + 1 : 1;

    return sprintf('%d%02d00', $difficulty, $next_question_num);
}
?>
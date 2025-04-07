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

// Fetch all questions from the database
$sql = "SELECT question_text, logged_question_id, user_id, class_name, competency_name, question_notes, date_added FROM logged_questions ORDER BY date_added DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Questions</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
<div class="logged_questions">
    <div class="container">
        <div class="left-box ">
            <h2>Logged Questions</h2>
            <div class="scrollable-container">
                            <table id="questionsTable">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>User ID</th>
                            <th>Class</th>
                            <th>Competency</th>
                            <th>Notes</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr data-id="<?php echo htmlspecialchars($row['logged_question_id']); ?>" onclick="selectQuestion(this)">
                            <td><?php echo htmlspecialchars($row['question_text']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['competency_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['question_notes']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_added']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                </table>


                <div class="button-container">
                <a href="#" class="large-button" onclick="addFinal()">Add Final</a>
                <a href="modify_question.php" class="large-button">Modify Question</a>
                <a href="#" class="large-button" onclick="deleteQuestion()">Delete Question</a>
                <a href="mainscreen.php" class="large-button">Back</a>
                </div>
    </div>
</div>

<script>
let selectedRow = null; // This variable keeps track of the selected row

function selectQuestion(row) {
    if (selectedRow) {
        selectedRow.classList.remove("selected");
    }
    selectedRow = row;
    row.classList.add("selected");
}

function deleteQuestion() {
    if (!selectedRow) {
        alert("Please select a question to delete.");
        return;
    }

    let questionId = selectedRow.getAttribute("data-id");

    fetch("deleteQuestion.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + questionId
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        selectedRow.remove();
        selectedRow = null;
    })
    .catch(error => console.error("Error:", error));
}


function addFinal() {
    if (!selectedRow) {
        alert("Please select a question to add as a final competency question.");
        return;
    }

    let questionId = selectedRow.getAttribute("data-id"); 

    
    fetch("addFinal.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + questionId
    })
    .then(response => response.text()) 
    .then(data => {
        alert(data);  

        
        if (data.includes("successfully added")) {
            selectedRow.remove();
            selectedRow = null;
        }
    })
    .catch(error => console.error("Error:", error));
}
</script>
</body>
</html>

<?php
$conn->close();
?>
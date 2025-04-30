<?php
require_once 'styleColor.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host   = 'localhost';
$dbUser = 'root';
$dbPass = ''; // No password
$dbName = 'databaseCS355';

// Create connection to the database
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Retrieve distinct class names for the dropdown
$class_query = "SELECT DISTINCT class_name FROM class_competency";
$class_result = $conn->query($class_query);

// Check for success and extract question details from query parameters
$success = isset($_GET['success']) && $_GET['success'] == 1;
if ($success) {
    $question = isset($_GET['question']) ? urldecode($_GET['question']) : '';
    $class = isset($_GET['class']) ? urldecode($_GET['class']) : '';
    $competency = isset($_GET['competency']) ? urldecode($_GET['competency']) : '';
    $subject = isset($_GET['subject']) ? urldecode($_GET['subject']) : '';
    $notes = isset($_GET['notes']) ? urldecode($_GET['notes']) : '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Oral Interview Add Question</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="add-question">
        <div class="form-container">
            <h1>New Question</h1>
            <form action="processAddQuestion.php" method="POST">
                <label for="question">Enter Your Question:</label>
                <textarea id="question" name="question" rows="4" placeholder="Type your question here..." required></textarea>
                
                <div class="dropdown-container">
                    <label for="class">Class:</label>
                    <select id="class" name="class" required>
                        <option value="">Select Class</option>
                        <?php
                        if ($class_result && $class_result->num_rows > 0) {
                            while ($row = $class_result->fetch_assoc()) {
                                $class = htmlspecialchars($row['class_name']);
                                echo "<option value=\"$class\">$class</option>";
                            }
                        }
                        ?>
                    </select>

                    <label for="competency">Competency:</label>
                    <select id="competency" name="competency" required>
                        <option value="">Select Competency</option>
                    </select>

                    <label for="subject">Subject:</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select Subject</option>
                    </select>

                    <label for="difficulty">Difficulty:</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="">Select Difficulty</option>
                        <option value="1">1 - Easiest</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4 - Most Challenging</option>
                    </select>
                </div>

                <label for="notes">Notes (Optional):</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Add any notes here..."></textarea>

                <div class="button-container">
                    <button class="submit-button" type="submit">Submit</button>
                    <button class="back-button" type="button" onclick="location.href='mainscreen.php';">Back</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript to update competency and subject dropdowns based on the selected class and competency -->
    <script>
    // Update competencies when a class is selected
    document.getElementById('class').addEventListener('change', function() {
        var selectedClass = this.value;
        var competencySelect = document.getElementById('competency');
        var subjectSelect = document.getElementById('subject');
        
        // Reset the competency and subject dropdowns to default option
        competencySelect.innerHTML = '<option value="">Select Competency</option>';
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        
        if (selectedClass !== '') {
            fetch('getCompetencies.php?class=' + encodeURIComponent(selectedClass))
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok: " + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Error in response:", data.error);
                        return;
                    }
                    // Populate competency dropdown with competencies for the selected class
                    data.competencies.forEach(function(comp) {
                        var option = document.createElement('option');
                        option.value = comp;
                        option.textContent = comp;
                        competencySelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching competencies:', error);
                });
        }
    });

    // Update subjects when a competency is selected
    document.getElementById('competency').addEventListener('change', function() {
        var selectedClass = document.getElementById('class').value;
        var selectedCompetency = this.value;
        var subjectSelect = document.getElementById('subject');
        
        // Reset the subject dropdown
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        
        if (selectedClass !== '' && selectedCompetency !== '') {
            fetch('getSubjects.php?class=' + encodeURIComponent(selectedClass) + '&competency=' + encodeURIComponent(selectedCompetency))
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok: " + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Error in response:", data.error);
                        return;
                    }
                    // Populate subject dropdown with subjects matching the selected class and competency
                    data.subjects.forEach(function(subj) {
                        var option = document.createElement('option');
                        option.value = subj;
                        option.textContent = subj;
                        subjectSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching subjects:', error);
                });
        }
    });
    // If question was successfully added, send it to the student view
    <?php if ($success): ?>
        if (window.opener && !window.opener.closed) {
            const questionData = {
                class_name: <?php echo json_encode($class); ?>,
                competency_name: <?php echo json_encode($competency); ?>,
                class_subject: <?php echo json_encode($subject); ?>,
                question_text: <?php echo json_encode($question); ?>,
                question_notes: <?php echo json_encode($notes); ?>
            };
            window.opener.displayLoggedQuestionInStudentView(questionData);
            window.close();
        }
    <?php endif; ?>
    </script>
</body>
</html>
<?php
$conn->close();
?>

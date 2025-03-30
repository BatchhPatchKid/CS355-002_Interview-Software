<?php
session_start();

// Database connection settings
$host   = 'localhost';
$dbUser = 'root';
$dbPass = ''; // No password
$dbName = 'databaseCS355';

// Create connection to the database
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve distinct class names
$class_query = "SELECT DISTINCT class_name FROM class_competency";
$class_result = $conn->query($class_query);

// Retrieve distinct competency names
$competency_query = "SELECT DISTINCT competency_name FROM class_competency";
$competency_result = $conn->query($competency_query);

// Retrieve distinct subjects (i.e. category_subject, now stored as class_subject)
$subject_query = "SELECT DISTINCT class_subject FROM class_competency";
$subject_result = $conn->query($subject_query);
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
                        <?php
                        if ($competency_result && $competency_result->num_rows > 0) {
                            while ($row = $competency_result->fetch_assoc()) {
                                $competency = htmlspecialchars($row['competency_name']);
                                echo "<option value=\"$competency\">$competency</option>";
                            }
                        }
                        ?>
                    </select>

                    <label for="subject">Subject:</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select Subject</option>
                        <?php
                        if ($subject_result && $subject_result->num_rows > 0) {
                            while ($row = $subject_result->fetch_assoc()) {
                                $subject = htmlspecialchars($row['class_subject']);
                                echo "<option value=\"$subject\">$subject</option>";
                            }
                        }
                        ?>
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
                    <button class="back-button" type="button" onclick="location.href='mainscreen.html';">Back</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>
<?php

session_start();
require_once 'styleColor.php';

// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle POST submission if this is also the processing page
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedClasses = $_POST['classes'] ?? [];
    $selectedCompetencies = $_POST['competencies'] ?? [];

    echo "<h2>You selected:</h2>";

    echo "<h3>Classes:</h3><ul>";
    foreach ($selectedClasses as $class) {
        echo "<li>Class: " . htmlspecialchars($class) . "</li>";
    }
    echo "</ul>";

    echo "<h3>Competencies:</h3><ul>";
    foreach ($selectedCompetencies as $comp) {
        echo "<li>" . htmlspecialchars($comp) . "</li>";
    }
    echo "</ul><hr>";
}

// Connect to the database
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch distinct classes
$class_query = "SELECT DISTINCT class_name FROM class_competency";
$class_result = $conn->query($class_query);

// Fetch distinct competencies
$competency_query = "SELECT DISTINCT competency_name FROM class_competency";
$competency_result = $conn->query($competency_query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Oral Interview Part 1</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Function to check if at least one class or one competency is selected
        function validateForm() {
            var selectedClasses = document.querySelectorAll('input[name="classes[]"]:checked');
            var selectedCompetencies = document.querySelectorAll('input[name="competencies[]"]:checked');

            if (selectedClasses.length === 0 && selectedCompetencies.length === 0) {
                alert("Please select at least one class or one competency to proceed.");
                return false;  // Prevent form submission
            }
            return true;  // Allow form submission
        }
    </script>
</head>

<body>
    <div class="main">
        <h1>Interview</h1>

        <form action="question_select.php" method="POST" onsubmit="return validateForm();">
            <fieldset>
                <legend>Choose Class(es):</legend>
                <?php
                if ($class_result->num_rows > 0) {
                    while ($row = $class_result->fetch_assoc()) {
                        $className = htmlspecialchars($row['class_name']);
                        echo "<label class='checkbox-label'>$className <input type='checkbox' name='classes[]' value='$className'></label><br>";
                    }
                } else {
                    echo "<p>No classes found.</p>";
                }
                ?>
            </fieldset>
            <br>

            <fieldset>
                <legend>Choose Competency(ies):</legend>
                <?php
                if ($competency_result->num_rows > 0) {
                    while ($row = $competency_result->fetch_assoc()) {
                        $compName = htmlspecialchars($row['competency_name']);
                        echo "<label class='checkbox-label'>$compName <input type='checkbox' name='competencies[]' value='$compName'></label><br>";
                    }
                } else {
                    echo "<p>No competencies found.</p>";
                }
                ?>
            </fieldset>
            <br>

            <div class="wrap">
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>
</body>

</html>

<?php
$conn->close();
?>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedClasses = $_POST['classes'] ?? [];
    $selectedCompetencies = $_POST['competencies'] ?? [];

    echo "<h2>You selected:</h2>";

    echo "<h3>Classes:</h3><ul>";
    foreach ($selectedClasses as $c) {
        list($class, $subject) = explode('|', $c);
        echo "<li>Class: " . htmlspecialchars($class) . " | Subject: " . htmlspecialchars($subject) . "</li>";
    }
    echo "</ul>";

    echo "<h3>Competencies:</h3><ul>";
    foreach ($selectedCompetencies as $comp) {
        echo "<li>" . htmlspecialchars($comp) . "</li>";
    }
    echo "</ul>";
} else {
    echo "Invalid request.";
}
?>
<?php
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
$class_query = "SELECT DISTINCT class_name, class_subject FROM class_competency";
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
</head>

<body>
    <div class="main">
        <h1>Interview</h1>

        <form action="submit_selection.php" method="POST">
            <fieldset>
                <legend>Choose Class(es):</legend>
                <?php
                if ($class_result->num_rows > 0) {
                    while ($row = $class_result->fetch_assoc()) {
                        $value = $row['class_name'] . '|' . $row['class_subject']; // Combine for easy passing
                        echo "<label><input type='checkbox' name='classes[]' value='$value'> " . htmlspecialchars($row['class_name']) . " (" . htmlspecialchars($row['class_subject']) . ")</label><br>";
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
                        echo "<label><input type='checkbox' name='competencies[]' value='" . htmlspecialchars($row['competency_name']) . "'> " . htmlspecialchars($row['competency_name']) . "</label><br>";
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

<?php
$host   = 'localhost';
$dbUser = 'root';
$dbPass = ''; // No password
$dbName = 'databaseCS355';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch questions
$query = "SELECT class_name, competency_name, class_subject, question_text, question_notes FROM logged_questions";
//Filters for attributes
$conditions = [];
$types = '';
$params = [];

if (!empty($selectedClassSubject)) {
    $conditions[] = "class_subject = ?";
    $types .= 's';
    $params[] = $selectedClassSubject;
}
if (!empty($selectedClasses)) {
    $conditions[] = "class_name IN (" . implode(',', array_fill(0, count($selectedClasses), '?')) . ")";
    $types .= str_repeat('s', count($selectedClasses));
    $params = array_merge($params, $selectedClasses);
}
if (!empty($selectedCompetencies)) {
    $conditions[] = "competency_name IN (" . implode(',', array_fill(0, count($selectedCompetencies), '?')) . ")";
    $types .= str_repeat('s', count($selectedCompetencies));
    $params = array_merge($params, $selectedCompetencies);
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$questions = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}

//Fetch classes and competencies
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get selected classes and competencies, default to empty arrays if not set
    $selectedClasses = $_POST['classes'] ?? [];
    $selectedCompetencies = $_POST['competencies'] ?? [];
}

// Fetch distinct class_subjects from logged_questions
$category_query = "SELECT DISTINCT class_subject FROM logged_questions";
$category_result = $conn->query($category_query);

$selectedClasses = $_POST['classes'] ?? [];
$selectedCompetencies = $_POST['competencies'] ?? [];
$selectedClassSubject = $_POST['class_subject'] ?? '';

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Oral Interview Question Select</title>
    <link rel="stylesheet" href="style.css">
    <script>
        let popupWindow;

        function openPopup() {
            const width = 1000;
            const height = 1000;
            const left = (window.innerWidth - width) / 2 + window.screenX;
            const top = (window.innerHeight - height) / 2 + window.screenY;
            popupWindow = window.open('', 'PopupWindow', `width=${width},height=${height},left=${left},top=${top}`);
            popupWindow.document.write(`
                <html>
                    <head>
                        <title>Student View</title>
                        <style>
                            body {
                                display: flex;
                                height: 100vh;
                                margin: 0;
                                justify-content: center;
                                align-items: center;
                                text-align: center;
                            }
                        </style>
                    </head>
                    <body>
                        <h1></h1>
                    </body>
                </html>
            `);
        }
        
        function updatePopup(content) {
            if (popupWindow && !popupWindow.closed) {
                popupWindow.document.body.innerHTML = `<h1>${content}</h1>`;
            } else {
                alert('Please open the student view first!');
            }
        }
        
        async function getRandomQuestion() {
            try {
                const response = await fetch('getRandomQuestion.php');
                const data = await response.json();
                if (data.error) {
                    alert(data.error);
                } else {
                    const content = `
                        <strong>Class:</strong> ${data.class_name}<br>
                        <strong>Competency:</strong> ${data.competency_name}<br>
                        <strong>Subject:</strong> ${data.class_subject}<br><br>
                        <strong>Question:</strong> ${data.question_text}<br>
                        <em>${data.question_notes || ""}</em>
                    `;
                    updatePopup(content);
                }
            } catch (err) {
                alert('Error fetching random question.');
                console.error(err);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="left-box">
            <div class="header-container">
                <button onclick="openPopup()">Open Student View</button>
                <h2>Class:</h2>
                <?php
                //display current class
                    if (!empty($selectedClasses)) {
                        foreach ($selectedClasses as $class) {
                            echo htmlspecialchars($class) . "<br>"; // Plain text, line break between each
                        }
                    } else {
                        echo "No classes selected.<br>";
                    }
                ?>
                <h2>Competency:</h2>
                <?php
                //display current competency
                    if (!empty($selectedCompetencies)) {
                        foreach ($selectedCompetencies as $comp) {
                            echo htmlspecialchars($comp) . "<br>"; // Plain text, line break between each
                        }
                    } else {
                        echo "No competencies selected.<br>";
                    }
                ?>
            </div>
            <div class="scrollable-container">
                <?php
                // Loop through each question and create a button.
                if (count($questions) > 0) {
                    foreach ($questions as $q) {
                        // Construct the HTML content for the question details.
                        $content = "<strong>Class:</strong> " . htmlspecialchars($q['class_name']) . "<br>" .
                                   "<strong>Competency:</strong> " . htmlspecialchars($q['competency_name']) . "<br>" .
                                   "<strong>Subject:</strong> " . htmlspecialchars($q['class_subject']) . "<br><br>" .
                                   "<strong>Question:</strong> " . htmlspecialchars($q['question_text']) . "<br>" .
                                   "<em>" . htmlspecialchars($q['question_notes']) . "</em>";
                        // Escape the content for the onClick attribute.
                        $jsContent = addslashes($content);
                        // Use a truncated preview of the question text as the button label.
                        $buttonLabel = substr(htmlspecialchars($q['question_text']), 0, 20) . "...";
                        echo "<button onclick=\"updatePopup('$jsContent')\">$buttonLabel</button>";
                    }
                } else {
                    echo "<p>No questions found.</p>";
                }
                ?>
            </div>
        </div>
        <div class="right-container">
            <div class="right-box">
                <div class="header-container">
                    <h2>Categories</h2>
                </div>


                <div class="scrollable-container" style="min-width: 35vw;">
                    <form action="question select.php" method="POST">
                        <?php
                        if ($category_result->num_rows > 0) {
                            while ($row = $category_result->fetch_assoc()) {
                                $classSubject = htmlspecialchars($row['class_subject']);
                                echo "<button class='question-button' type='submit' name='class_subject' value='$classSubject'>$classSubject</button>";
                            }
                        } else {
                            echo "<button class='question-button' type='button'>No categories found</button>";
                        }
                        ?>
                        <!-- Preserve classes and competencies -->
                        <?php
                        foreach ($selectedClasses as $class) {
                            echo "<input type='hidden' name='classes[]' value='" . htmlspecialchars($class) . "'>";
                        }
                        foreach ($selectedCompetencies as $comp) {
                            echo "<input type='hidden' name='competencies[]' value='" . htmlspecialchars($comp) . "'>";
                        }
                        ?>
                    </form>
                </div>


            </div>
            <div class="button-box">
                <button class="large-button" onclick="getRandomQuestion()">Random</button>
                <button class="large-button" onclick="location.href='mainscreen.php'">End Interview</button>
            </div>
        </div>
    </div>
</body>
</html>

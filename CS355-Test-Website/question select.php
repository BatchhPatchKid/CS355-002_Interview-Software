<?php
session_start();

// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection settings
$host = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'databaseCS355';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch questions based on selected classes and competencies
$whereClauses = [];

if (!empty($_POST['classes'])) {
    $selectedClasses = $_POST['classes'];
    $whereClauses[] = "class_name IN ('" . implode("','", array_map(function($class) use ($conn) {
        return mysqli_real_escape_string($conn, $class);
    }, $selectedClasses)) . "')";
}

if (!empty($_POST['competencies'])) {
    $selectedCompetencies = $_POST['competencies'];
    $whereClauses[] = "competency_name IN ('" . implode("','", array_map(function($comp) use ($conn) {
        return mysqli_real_escape_string($conn, $comp);
    }, $selectedCompetencies)) . "')";
}

// Check if a specific class_subject is selected via GET
$classSubjectFilter = isset($_GET['class_subject']) ? $_GET['class_subject'] : null;

if ($classSubjectFilter) {
    $whereClauses[] = "class_subject = '" . mysqli_real_escape_string($conn, $classSubjectFilter) . "'";
}

// Build the final query with filtering
$query = "SELECT class_name, competency_name, class_subject, question_text, question_notes FROM competency_questions";
if (count($whereClauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$result = $conn->query($query);

$questions = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
$conn->close();

// Get selected class and competency names
$selectedClasses = isset($_POST['classes']) ? $_POST['classes'] : [];
$selectedCompetencies = isset($_POST['competencies']) ? $_POST['competencies'] : [];

$uniqueSubjects = [];
foreach ($questions as $q) {
    $uniqueSubjects[$q['class_subject']] = $q['class_subject'];
}
$uniqueSubjects = array_values($uniqueSubjects); // Get unique subjects as an indexed array
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

        function toggleSubject(subject) {
            let url = new URL(window.location.href);
            if (url.searchParams.get('class_subject') === subject) {
                url.searchParams.delete('class_subject'); // Remove filter if clicked again
            } else {
                url.searchParams.set('class_subject', subject); // Set the filter
            }
            window.location.href = url.toString(); // Reload with updated URL
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h2>Class: <?php echo implode(", ", array_map('htmlspecialchars', $selectedClasses)); ?></h2>
            <h2>Competency: <?php echo implode(", ", array_map('htmlspecialchars', $selectedCompetencies)); ?></h2>
        </div>

        <div class="left-box">
            <div class="scrollable-container">
                <button onclick="openPopup()">Open Student View</button>
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
                <div class="scrollable-container">
                    <?php
                    // Loop through unique subjects and create a button for each
                    foreach ($uniqueSubjects as $subject) {
                        echo "<button class=\"question-button\" type=\"button\" onclick=\"toggleSubject('$subject')\">$subject</button>";
                    }
                    ?>
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
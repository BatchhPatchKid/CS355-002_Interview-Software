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
$selectedClasses = $_POST['classes'] ?? $_GET['classes'] ?? [];
$selectedCompetencies = $_POST['competencies'] ?? $_GET['competencies'] ?? [];

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
        let filteredQuestions = <?php echo json_encode($questions); ?>;

        function openPopup() {
    const width = 1000;
    const height = 1000;
    const left = (window.innerWidth - width) / 2 + window.screenX;
    const top = (window.innerHeight - height) / 2 + window.screenY;

    if (!window.popupWindow || window.popupWindow.closed) {
        window.popupWindow = window.open('', 'PopupWindow', `width=${width},height=${height},left=${left},top=${top}`);

        // Initialize a basic layout with an updatable content div
        window.popupWindow.document.write(`
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
                            font-family: sans-serif;
                        }
                        #popupContent {
                            padding: 20px;
                            max-width: 80%;
                        }
                    </style>
                </head>
                <body>
                    <div id="popupContent">Waiting for question...</div>
                </body>
            </html>
        `);
    } else {
        window.popupWindow.focus();
    }
}

function updatePopup(content) {
    if (window.popupWindow && !window.popupWindow.closed) {
        const popupDoc = window.popupWindow.document;
        const contentDiv = popupDoc.getElementById('popupContent');

        if (contentDiv) {
            contentDiv.innerHTML = content;
        } else {
            // Recreate the div if somehow missing
            popupDoc.body.innerHTML = `<div id="popupContent">${content}</div>`;
        }
    } else {
        alert('Please open the student view first!');
    }
}



        function showSelectedQuestion(content) {
            document.getElementById("selectedQuestionDisplay").innerHTML = content;
        }

        function getRandomQuestion() {
            if (!filteredQuestions || filteredQuestions.length === 0) {
                alert("No questions available to select from.");
                return;
            }
            const randomIndex = Math.floor(Math.random() * filteredQuestions.length);
            const data = filteredQuestions[randomIndex];
            const content = `
                <strong>Class:</strong> ${data.class_name}<br>
                <strong>Competency:</strong> ${data.competency_name}<br>
                <strong>Subject:</strong> ${data.class_subject}<br><br>
                <strong>Question:</strong> ${data.question_text}<br>
                <em>${data.question_notes || ""}</em>
            `;
            showSelectedQuestion(content);
            updatePopup(content);
        }

        function toggleSubject(subject) {
            const form = document.getElementById('categoryForm');
            const currentUrl = new URL(window.location.href);

            const currentSubject = currentUrl.searchParams.get('class_subject');

            const newUrl = new URL(window.location.href);
            newUrl.search = ''; 

            if (currentSubject !== subject) {
                newUrl.searchParams.set('class_subject', subject);
            }
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                newUrl.searchParams.append(key, value);
            }

            window.location.href = newUrl.toString();
        }
    let stopwatchSeconds = 0;
    function updateStopwatch() {
        stopwatchSeconds++;
        const mins = Math.floor(stopwatchSeconds / 60).toString().padStart(2, '0');
        const secs = (stopwatchSeconds % 60).toString().padStart(2, '0');
        document.getElementById('stopwatch').textContent = `${mins}:${secs}`;
    }
    setInterval(updateStopwatch, 1000);

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
                if (count($questions) > 0) {
                    foreach ($questions as $q) {
                        $content = "<strong>Class:</strong> " . htmlspecialchars($q['class_name']) . "<br>" .
                                   "<strong>Competency:</strong> " . htmlspecialchars($q['competency_name']) . "<br>" .
                                   "<strong>Subject:</strong> " . htmlspecialchars($q['class_subject']) . "<br><br>" .
                                   "<strong>Question:</strong> " . htmlspecialchars($q['question_text']) . "<br>" .
                                   "<em>" . htmlspecialchars($q['question_notes']) . "</em>";
                        $jsContent = addslashes($content);
                        $buttonLabel = substr(htmlspecialchars($q['question_text']), 0, 20) . "...";
                        echo "<button onclick=\"showSelectedQuestion('$jsContent'); updatePopup('$jsContent');\">$buttonLabel</button>";
                    }
                } else {
                    echo "<p>No questions found.</p>";
                }
                ?>
            </div>
            <div id="stopwatchContainer" style="margin-top: 10px; text-align: left; padding: 10px;">
    ⏱️       <span id="stopwatch">00:00</span>
</div>

        </div>

        <div class="right-container">
            <div class="right-box">
                <div class="header-container">
                    <h2>Categories</h2>
                </div>

                <form id="categoryForm">
                        <?php
                        foreach ($selectedClasses as $cls) {
                            echo '<input type="hidden" name="classes[]" value="' . htmlspecialchars($cls) . '">';
                        }
                        foreach ($selectedCompetencies as $comp) {
                            echo '<input type="hidden" name="competencies[]" value="' . htmlspecialchars($comp) . '">';
                        }
                        ?>
                    </form>

                <div class="scrollable-container">
                    <?php
                    foreach ($uniqueSubjects as $subject) {
                        echo "<button class=\"question-button\" type=\"button\" onclick=\"toggleSubject('$subject')\">$subject</button>";
                    }
                    ?>
                </div>
            </div>

            <div id="selectedQuestionDisplay" style="margin: 15px; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9;"></div>

            <div class="button-box">
                <button class="large-button" onclick="getRandomQuestion()">Random</button>
                <button class="large-button" onclick="location.href='mainscreen.php'">End Interview</button>
            </div>
        </div>
    </div>
</body>
</html>

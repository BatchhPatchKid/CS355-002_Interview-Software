<?php
require_once 'styleColor.php';

// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Save POST selections to session so they persist across reloads
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION['selected_classes'] = $_POST['classes'] ?? [];
    $_SESSION['selected_competencies'] = $_POST['competencies'] ?? [];
}

// Use session data for selections
$selectedClasses = $_SESSION['selected_classes'] ?? [];
$selectedCompetencies = $_SESSION['selected_competencies'] ?? [];

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

if (!empty($selectedClasses)) {
    $whereClauses[] = "class_name IN ('" . implode("','", array_map(function($class) use ($conn) {
        return mysqli_real_escape_string($conn, $class);
    }, $selectedClasses)) . "')";
}

if (!empty($selectedCompetencies)) {
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

// Extract unique class_subject values
$uniqueSubjects = [];
foreach ($questions as $q) {
    $uniqueSubjects[$q['class_subject']] = $q['class_subject'];
}
$uniqueSubjects = array_values($uniqueSubjects);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Oral Interview Question Select</title>
    <link rel="stylesheet" href="style.css">
    <script>
        let popupWindow;
        let filteredQuestions = <?php echo json_encode($questions); ?>;

       // window.addEventListener('load', () => {
       // if (!window.popupWindow || window.popupWindow.closed) {
        //window.popupWindow = window.open('', 'PopupWindow');
        //}
       // });

        function openPopup() {
    const width = 1000;
    const height = 1000;
    const left = (window.innerWidth - width) / 2 + window.screenX;
    const top = (window.innerHeight - height) / 2 + window.screenY;

    // Check if a popup is already open (and if it is, don't create a new one)
    if (window.popupWindow && !window.popupWindow.closed) {
        window.popupWindow.focus();
        localStorage.setItem("studentPopupOpen", "1"); // Save state
        return;
    }

    try {
        window.popupWindow = window.open('', 'PopupWindow', `width=${width},height=${height},left=${left},top=${top}`);
        // Save state indicating the popup is open
        localStorage.setItem("studentPopupOpen", "1");

        if (window.popupWindow.document.body.innerHTML.trim() === '') {
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
        }
    } catch (e) {
        alert("Popup blocked. Please allow popups for this site.");
    }
}

function updatePopup(content) {
    // Check if the popup state is stored in localStorage
    if (localStorage.getItem("studentPopupOpen") === "1") {
        if (window.popupWindow && !window.popupWindow.closed) {
            const popupDoc = window.popupWindow.document;
            const contentDiv = popupDoc.getElementById('popupContent');
            
            if (contentDiv) {
                contentDiv.innerHTML = content;
            } else {
                // If contentDiv is missing, recreate it
                popupDoc.body.innerHTML = `<div id="popupContent">${content}</div>`;
            }
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
            let url = new URL(window.location.href);
            if (url.searchParams.get('class_subject') === subject) {
                url.searchParams.delete('class_subject');
            } else {
                url.searchParams.set('class_subject', subject);
            }
            window.location.href = url.toString();
        }

        // Display newly logged question in student view
        window.displayLoggedQuestionInStudentView = function(data) {
            const content = `
                <strong>Class:</strong> ${data.class_name}<br>
                <strong>Competency:</strong> ${data.competency_name}<br>
                <strong>Subject:</strong> ${data.class_subject}<br><br>
                <strong>Question:</strong> ${data.question_text}<br>
                <em>${data.question_notes || ''}</em>
            `;
            updatePopup(content);
        };

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
            <div id="followUpContainer" style="margin-top: 10px; padding: 10px; border: 1px solid #ccc; background-color: #f0f0f0;">
                <strong>Follow-up Questions:</strong>
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
                <button class="large-button" onclick="window.open('addQuestion.php', '_blank')">Log New Question</button>
                <button class="large-button" onclick="location.href='mainscreen.php'">End Interview</button>
            </div>
        </div>
    </div>
</body>
</html>


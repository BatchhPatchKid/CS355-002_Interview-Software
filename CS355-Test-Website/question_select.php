<?php
require_once 'styleColor.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION['selected_classes'] = $_POST['classes'] ?? [];
    $_SESSION['selected_competencies'] = $_POST['competencies'] ?? [];
}

$selectedClasses = $_SESSION['selected_classes'] ?? [];
$selectedCompetencies = $_SESSION['selected_competencies'] ?? [];

$host = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'databaseCS355';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

$classSubjectFilter = isset($_GET['class_subject']) ? $_GET['class_subject'] : null;

if ($classSubjectFilter) {
    $whereClauses[] = "class_subject = '" . mysqli_real_escape_string($conn, $classSubjectFilter) . "'";
}

$query = "SELECT * FROM competency_questions";
if (count($whereClauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$result = $conn->query($query);
$questions = [];
$followUps = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['parent_id'])) {
            $followUps[$row['parent_id']][] = $row;
        } else {
            $questions[] = $row;
        }
    }
}
$conn->close();

$uniqueSubjects = [];
foreach (array_merge($questions, ...array_values($followUps)) as $q) {
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
        const questions = <?php echo json_encode($questions); ?>;
        const followUps = <?php echo json_encode($followUps); ?>;

        function openPopup() {
            const width = 1000;
            const height = 1000;
            const left = (window.innerWidth - width) / 2 + window.screenX;
            const top = (window.innerHeight - height) / 2 + window.screenY;

            if (window.popupWindow && !window.popupWindow.closed) {
                window.popupWindow.focus();
                localStorage.setItem("studentPopupOpen", "1");
                return;
            }

            try {
                window.popupWindow = window.open('', 'PopupWindow', `width=${width},height=${height},left=${left},top=${top}`);
                localStorage.setItem("studentPopupOpen", "1");

                if (window.popupWindow.document.body.innerHTML.trim() === '') {
                    window.popupWindow.document.write(`
                        <html>
                        <head>
                            <title>Student View</title>
                            <style>
                                body { display: flex; height: 100vh; margin: 0; justify-content: center; align-items: center; font-family: sans-serif; }
                                #popupContent { padding: 20px; max-width: 80%; }
                            </style>
                        </head>
                        <body><div id="popupContent">Waiting for question...</div></body></html>
                    `);
                }
            } catch (e) {
                alert("Popup blocked. Please allow popups for this site.");
            }
        }

        function updatePopup(content) {
            if (localStorage.getItem("studentPopupOpen") === "1") {
                if (window.popupWindow && !window.popupWindow.closed) {
                    const popupDoc = window.popupWindow.document;
                    const contentDiv = popupDoc.getElementById('popupContent');
                    if (contentDiv) {
                        contentDiv.innerHTML = content;
                    } else {
                        popupDoc.body.innerHTML = `<div id="popupContent">${content}</div>`;
                    }
                }
            } else {
                alert('Please open the student view first!');
            }
        }

        function displayLoggedQuestionInStudentView(data) {
            const content = `
                <strong>Class:</strong> ${data.class_name}<br>
                <strong>Competency:</strong> ${data.competency_name}<br>
                <strong>Subject:</strong> ${data.class_subject}<br><br>
                <strong>Question:</strong> ${data.question_text}<br>
                <em>${data.question_notes || ''}</em>
            `;
            updatePopup(content);
        }

        function showFollowUps(parentId) {
            const container = document.getElementById("followUpContainer");
            container.innerHTML = "<strong>Follow-up Questions:</strong><br>";
            if (followUps[parentId]) {
                followUps[parentId].forEach(q => {
                    const btn = document.createElement("button");
                    btn.textContent = q.question_text.substring(0, 20) + '...';
                    btn.onclick = () => {
                        const content = `
                            <strong>Class:</strong> ${q.class_name}<br>
                            <strong>Competency:</strong> ${q.competency_name}<br>
                            <strong>Subject:</strong> ${q.class_subject}<br><br>
                            <strong>Question:</strong> ${q.question_text}<br>
                            <em>${q.question_notes || ''}</em>`;
                        updatePopup(content);
                    };
                    container.appendChild(btn);
                });
            }
        }

        function showSelectedQuestion(q) {
            const content = `
                <strong>Class:</strong> ${q.class_name}<br>
                <strong>Competency:</strong> ${q.competency_name}<br>
                <strong>Subject:</strong> ${q.class_subject}<br><br>
                <strong>Question:</strong> ${q.question_text}<br>
                <em>${q.question_notes || ''}</em>
            `;
            document.getElementById("selectedQuestionDisplay").innerHTML = content;
            updatePopup(content);
            showFollowUps(q.question_id);
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

        function getRandomQuestion() {
            if (!questions.length) return alert("No questions available.");
            const q = questions[Math.floor(Math.random() * questions.length)];
            showSelectedQuestion(q);
        }
    </script>
</head>
<body>
<div class="container">
    <div class="header-container">
        <h2>Class: <?= implode(", ", array_map('htmlspecialchars', $selectedClasses)); ?></h2>
        <h2>Competency: <?= implode(", ", array_map('htmlspecialchars', $selectedCompetencies)); ?></h2>
    </div>

    <div class="left-box">
        <div class="scrollable-container">
            <button onclick="openPopup()">Open Student View</button>
            <?php
            foreach ($questions as $q) {
                $label = htmlspecialchars(substr($q['question_text'], 0, 20)) . "...";
                echo "<button onclick='showSelectedQuestion(" . json_encode($q) . ")'>$label</button>";
            }
            ?>
        </div>
        <div id="followUpContainer" style="margin-top:10px; padding:10px; border:1px solid #ccc; background:#f0f0f0;">
            <strong>Follow-up Questions:</strong>
        </div>
        <div id="stopwatchContainer" style="margin-top: 10px; text-align: left; padding: 10px;">
            ⏱️ <span id="stopwatch">00:00</span>
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
                    echo "<button class=\"question-button\" onclick=\"toggleSubject('$subject')\">$subject</button>";
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
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

// Extract unique subjects
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
    <style>
        body, button, #popupContent, #selectedQuestionDisplay {
            font-size: 18px;
        }
        /* Ensure popup content is larger */
        #popupContent {
            padding: 20px;
            max-width: 80%;
            font-size: 20px;
        }
        .question-btn, .large-button, .scrollable-container button {
            font-size: 16px;
        }
    </style>
    <script>
        let popupWindow;
        const questions = <?php echo json_encode($questions, JSON_HEX_TAG); ?>;
        const followUps = <?php echo json_encode($followUps, JSON_HEX_TAG); ?>;

        function openPopup() {
            const width = 1000, height = 1000;
            const left = (window.innerWidth - width) / 2 + window.screenX;
            const top  = (window.innerHeight - height) / 2 + window.screenY;

            if (popupWindow && !popupWindow.closed) {
                popupWindow.focus();
                localStorage.setItem("studentPopupOpen", "1");
                return;
            }

            popupWindow = window.open('', 'PopupWindow', `width=${width},height=${height},left=${left},top=${top}`);
            localStorage.setItem("studentPopupOpen", "1");
            popupWindow.document.write(`
                <html>
                <head><title>Student View</title>
                <style>
                    body { display:flex; height:100vh; margin:0; justify-content:center; align-items:center; font-family:sans-serif; font-size: 20px; }
                    #popupContent { padding:20px; max-width:80%; font-size: 22px; }
                </style>
                </head>
                <body><div id="popupContent">Waiting for question...</div></body>
                </html>
            `);
        }

        function updatePopup(content) {
            if (localStorage.getItem("studentPopupOpen") === "1"
                && popupWindow && !popupWindow.closed) {
                const el = popupWindow.document.getElementById('popupContent');
                if (el) el.textContent = content;
                else popupWindow.document.body.innerHTML = `<div id="popupContent">${content}</div>`;
            } else {
                alert('Please open the student view first!');
            }
        }

        function formatDisplay(q) {
            return `
                <strong>Class:</strong> ${q.class_name}<br>
                <strong>Competency:</strong> ${q.competency_name}<br>
                <strong>Subject:</strong> ${q.class_subject}<br><br>
                <strong>Question:</strong> ${q.question_text}<br>
                <em>${q.question_notes || ''}</em>
            `;
        }

        function showFollowUps(parentId) {
            const container = document.getElementById("followUpContainer");
            container.innerHTML = "<strong>Follow-up Questions:</strong><br>";
            (followUps[parentId] || []).forEach(q => {
                const btn = document.createElement("button");
                btn.textContent = q.question_text.slice(0, 20) + '...';
                btn.onclick = () => {
                    document.getElementById('selectedQuestionDisplay').innerHTML = formatDisplay(q);
                    updatePopup(q.question_text);
                    showFollowUps(q.question_id);
                };
                container.appendChild(btn);
            });
        }

        function showSelectedQuestion(q) {
            document.getElementById("selectedQuestionDisplay").innerHTML = formatDisplay(q);
            updatePopup(q.question_text);
            showFollowUps(q.question_id);
        }

        function filterQuestions(subject) {
            document.querySelectorAll('.question-btn').forEach(btn => {
                btn.style.display = (subject === 'All' || btn.dataset.subject === subject) ? '' : 'none';
            });
            document.getElementById("selectedQuestionDisplay").innerHTML = '';
            document.getElementById("followUpContainer").innerHTML = '<strong>Follow-up Questions:</strong>';
        }

        function getRandomQuestion() {
            const visible = Array.from(document.querySelectorAll('.question-btn'))
                .filter(btn => btn.style.display !== 'none');
            if (!visible.length) return alert("No questions available.");
            visible[Math.floor(Math.random() * visible.length)].click();
        }

        let stopwatchSeconds = 0;
        function updateStopwatch() {
            stopwatchSeconds++;
            const m = String(Math.floor(stopwatchSeconds/60)).padStart(2,'0');
            const s = String(stopwatchSeconds%60).padStart(2,'0');
            document.getElementById('stopwatch').textContent = `${m}:${s}`;
        }
        setInterval(updateStopwatch, 1000);
    </script>
</head>
<body>
<div class="container" style="font-size:18px;">
    <div class="header-container" style="font-size:20px;">
        <h2>Class: <?= implode(", ", array_map('htmlspecialchars', $selectedClasses)) ?></h2>
        <h2>Competency: <?= implode(", ", array_map('htmlspecialchars', $selectedCompetencies)) ?></h2>
    </div>

    <div class="left-box">
        <div class="scrollable-container">
            <button onclick="openPopup()" style="font-size:16px;">Open Student View</button>
            <?php foreach ($questions as $q): ?>
                <button
                    class="question-btn"
                    data-subject="<?= htmlspecialchars($q['class_subject']) ?>"
                    onclick='showSelectedQuestion(<?= json_encode($q, JSON_HEX_TAG) ?>)'
                    style="font-size:16px;">
                    <?= htmlspecialchars(substr($q['question_text'], 0, 20)) ?>...
                </button>
            <?php endforeach; ?>
        </div>
        <div id="followUpContainer" style="margin-top:10px; padding:10px; border:1px solid #ccc; background:#f0f0f0; font-size:16px;">
            <strong>Follow-up Questions:</strong>
        </div>
        <div id="stopwatchContainer" style="margin-top: 10px; text-align: left; padding: 10px; font-size:16px;">
            ⏱️ <span id="stopwatch">00:00</span>
        </div>
    </div>

    <div class="right-container">
        <div class="right-box">
            <div class="header-container" style="font-size:18px;">
                <h2>Categories</h2>
            </div>
            <div class="scrollable-container">
                <button onclick="filterQuestions('All')" style="font-size:16px;">All</button>
                <?php foreach ($uniqueSubjects as $subject): ?>
                    <button onclick="filterQuestions('<?= htmlspecialchars($subject) ?>')" style="font-size:16px;">
                        <?= htmlspecialchars($subject) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="selectedQuestionDisplay" style="margin: 15px; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9; font-size:18px;">
        </div>

        <div class="button-box">
            <button class="large-button" onclick="getRandomQuestion()" style="font-size:16px;">Random</button>
            <button class="large-button" onclick="window.open('addQuestion.php', '_blank')" style="font-size:16px;">Log New Question</button>
            <button class="large-button" onclick="location.href='mainscreen.php'" style="font-size:16px;">End Interview</button>
        </div>
    </div>
</div>
</body>
</html>
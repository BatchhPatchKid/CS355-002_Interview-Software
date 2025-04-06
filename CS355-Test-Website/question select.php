<?php
$host   = 'localhost';
$dbUser = 'root';
$dbPass = ''; // No password
$dbName = 'databaseCS355';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch all questions. Adjust the query as needed (e.g., add ORDER BY RAND() for random order).
$query = "SELECT class_name, competency_name, class_subject, question_text, question_notes FROM logged_questions";
$result = $conn->query($query);

$questions = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
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
                <h2>Competency:</h2>
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
                <div class="scrollable-container">
                    <button class="question-button" type="submit">Category 1</button>
                    <button class="question-button" type="submit">Category 2</button>
                    <button class="question-button" type="submit">Category 3</button>
                    <button class="question-button" type="submit">Category 4</button>
                    <button class="question-button" type="submit">Category 5</button>
                    <button class="question-button" type="submit">Category 6</button>
                    <button class="question-button" type="submit">Category 7</button>
                    <button class="question-button" type="submit">Category 8</button>
                    <button class="question-button" type="submit">Category 9</button>
                    <button class="question-button" type="submit">Category 10</button>
                    <button class="question-button" type="submit">Category 11</button>
                    <button class="question-button" type="submit">Category 12</button>
                </div>
            </div>
            <div class="button-box">
                <button class="large-button" onclick="getRandomQuestion()">Random</button>
                <button class="large-button" onclick="location.href='mainscreen.html'">End Interview</button>
            </div>
        </div>
    </div>
</body>
</html>

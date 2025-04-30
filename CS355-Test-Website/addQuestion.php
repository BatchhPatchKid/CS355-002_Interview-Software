<?php
require_once 'styleColor.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host   = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'databaseCS355';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$class_query = "SELECT DISTINCT class_name FROM class_competency ORDER BY class_name";
$class_result = $conn->query($class_query);

$parent_query = "SELECT question_id, question_text FROM competency_questions ORDER BY date_added DESC";
$parent_result = $conn->query($parent_query);

$success = isset($_GET['success']) && $_GET['success'] == 1;
if ($success) {
    $question = isset($_GET['question']) ? urldecode($_GET['question']) : '';
    $class = isset($_GET['class']) ? urldecode($_GET['class']) : '';
    $competency = isset($_GET['competency']) ? urldecode($_GET['competency']) : '';
    $subject = isset($_GET['subject']) ? urldecode($_GET['subject']) : '';
    $notes = isset($_GET['notes']) ? urldecode($_GET['notes']) : '';
}
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
      <textarea id="question" name="question" rows="4" required></textarea>

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
        </select>

        <label for="subject">Subject:</label>
        <select id="subject" name="subject" required>
          <option value="">Select Subject</option>
        </select>

        <label for="difficulty">Difficulty:</label>
        <select id="difficulty" name="difficulty" required>
          <option value="">Select Difficulty</option>
          <option value="1">1 - Easiest</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4 - Most Challenging</option>
        </select>

        <label for="parent_id">Follow-Up To:</label>
        <select id="parent_id" name="parent_id">
          <option value="">None (standalone)</option>
          <?php
          if ($parent_result && $parent_result->num_rows > 0) {
              while ($row = $parent_result->fetch_assoc()) {
                  $pid = htmlspecialchars($row['question_id']);
                  $ptxt = htmlspecialchars(substr($row['question_text'], 0, 60));
                  echo "<option value=\"$pid\">$ptxt ($pid)</option>";
              }
          }
          ?>
        </select>
      </div>

      <label for="notes">Notes (Optional):</label>
      <textarea id="notes" name="notes" rows="2"></textarea>

      <div class="button-container">
        <button class="submit-button" type="submit">Submit</button>
        <button class="back-button" type="button" onclick="location.href='mainscreen.php';">Back</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('class').addEventListener('change', function() {
    var selectedClass = this.value;
    var competencySelect = document.getElementById('competency');
    var subjectSelect = document.getElementById('subject');

    competencySelect.innerHTML = '<option value="">Select Competency</option>';
    subjectSelect.innerHTML = '<option value="">Select Subject</option>';

    if (selectedClass !== '') {
      fetch('getCompetencies.php?class=' + encodeURIComponent(selectedClass))
        .then(response => response.json())
        .then(data => {
          data.competencies.forEach(function(comp) {
            var option = document.createElement('option');
            option.value = comp;
            option.textContent = comp;
            competencySelect.appendChild(option);
          });
        });
    }
  });

  document.getElementById('competency').addEventListener('change', function() {
    var selectedClass = document.getElementById('class').value;
    var selectedCompetency = this.value;
    var subjectSelect = document.getElementById('subject');

    subjectSelect.innerHTML = '<option value="">Select Subject</option>';

    if (selectedClass !== '' && selectedCompetency !== '') {
      fetch('getSubjects.php?class=' + encodeURIComponent(selectedClass) + '&competency=' + encodeURIComponent(selectedCompetency))
        .then(response => response.json())
        .then(data => {
          data.subjects.forEach(function(subj) {
            var option = document.createElement('option');
            option.value = subj;
            option.textContent = subj;
            subjectSelect.appendChild(option);
          });
        });
    }
  });
</script>
</body>
</html>
<?php $conn->close(); ?>
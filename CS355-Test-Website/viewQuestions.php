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
$allClasses = [];
if ($class_result) {
    while ($r = $class_result->fetch_assoc()) {
        $allClasses[] = $r['class_name'];
    }
}

$sql = "SELECT question_text, question_id, user_id, class_name, competency_name, class_subject, question_notes, parent_id, date_added FROM competency_questions ORDER BY class_name DESC";
$result = $conn->query($sql);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>View & Edit Questions</title>
  <link rel="stylesheet" href="style.css">
  <style>
    td.editing-cell { padding: 4px; }
  </style>
</head>
<body>
<div class="logged_questions">
  <div class="container">
    <div class="left-box">
      <div class="header-container">
        <h2>Competency Questions</h2>
      </div>
      <div class="scrollable-container">
        <table id="questionsTable">
          <thead>
            <tr>
              <th>Question</th><th>Question ID</th><th>User ID</th><th>Class</th>
              <th>Competency</th><th>Subject</th><th>Notes</th><th>Parent ID</th><th>Date Added</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = $result->fetch_assoc()) { ?>
            <tr data-id="<?=htmlspecialchars($row['question_id'])?>" onclick="selectQuestion(this)">
              <td><?=htmlspecialchars($row['question_text'])?></td>
              <td><?=htmlspecialchars($row['question_id'])?></td>
              <td><?=htmlspecialchars($row['user_id'])?></td>
              <td><?=htmlspecialchars($row['class_name'])?></td>
              <td><?=htmlspecialchars($row['competency_name'])?></td>
              <td><?=htmlspecialchars($row['class_subject'])?></td>
              <td><?=htmlspecialchars($row['question_notes'])?></td>
              <td><?=htmlspecialchars($row['parent_id'] ?: 'X')?></td>
              <td><?=htmlspecialchars($row['date_added'])?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="button-container">
        <button class="large-button modify" onclick="modifyQuestion()">Modify</button>
        <button class="large-button save"   onclick="saveEdit()" style="display:none;">Save</button>
        <button class="large-button cancel" onclick="cancelEdit()" style="display:none;">Cancel</button>
        <button class="large-button" onclick="deleteQuestion()">Delete</button>
        <button class="large-button" onclick="window.location='mainscreen.php'">Back</button>
      </div>
    </div>
  </div>
</div>
<script>
  const allClasses = <?= json_encode($allClasses, JSON_HEX_TAG) ?>;
  let selectedRow, originalValues;

  function selectQuestion(row) {
    if (document.querySelector('.save').style.display !== 'none') return;
    if (selectedRow) selectedRow.classList.remove("selected");
    selectedRow = row;
    row.classList.add("selected");
  }

  function modifyQuestion() {
    if (!selectedRow) { alert("Select a question."); return; }
    originalValues = {};
    [0,3,4,5,6].forEach(i => {
      const cell = selectedRow.cells[i];
      originalValues[i] = cell.innerText.trim();
      cell.classList.add("editing-cell");
      if (i === 3) {
        const sel = document.createElement('select');
        allClasses.forEach(v => {
          const o = document.createElement('option');
          o.value = o.textContent = v;
          if (v === cell.innerText.trim()) o.selected = true;
          sel.appendChild(o);
        });
        cell.innerHTML = '';
        cell.appendChild(sel);
      } else {
        cell.contentEditable = true;
      }
    });
    document.querySelector('.modify').style.display = 'none';
    document.querySelector('.save').style.display   = '';
    document.querySelector('.cancel').style.display = '';
  }

  function cancelEdit() {
    [0,3,4,5,6].forEach(i => {
      const cell = selectedRow.cells[i];
      cell.innerText       = originalValues[i];
      cell.contentEditable = false;
      cell.classList.remove("editing-cell");
    });
    selectedRow.classList.remove("selected");
    selectedRow = null;
    document.querySelector('.save').style.display   = 'none';
    document.querySelector('.cancel').style.display = 'none';
    document.querySelector('.modify').style.display = 'inline-block';
  }

  function saveEdit() {
    const id   = encodeURIComponent(selectedRow.dataset.id);
    const vals = [0,3,4,5,6].map(i => {
      const cell = selectedRow.cells[i];
      return (i === 3) ? cell.firstChild.value.trim() : cell.innerText.trim();
    });
    const [qText, cls, comp, subj, notes] = vals;
    const body = `table=competency_questions&id=${id}` +
                 `&question_text=${encodeURIComponent(qText)}` +
                 `&class_name=${encodeURIComponent(cls)}` +
                 `&competency_name=${encodeURIComponent(comp)}` +
                 `&class_subject=${encodeURIComponent(subj)}` +
                 `&question_notes=${encodeURIComponent(notes)}`;

    fetch("updateQuestion.php", {
      method: "POST",
      headers: {"Content-Type":"application/x-www-form-urlencoded"},
      body
    })
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'success') throw new Error(res.message||'Update failed');
      window.location.reload();
    })
    .catch(err => {
      alert("Error: " + err.message);
      cancelEdit();
    });
  }

  function deleteQuestion() {
    if (!selectedRow) { alert("Select a question."); return; }
    const id = encodeURIComponent(selectedRow.dataset.id);
    fetch("deleteQuestion.php", {
      method: "POST",
      headers: {"Content-Type":"application/x-www-form-urlencoded"},
      body: `table=competency_questions&id=${id}`
    })
    .then(r => r.text())
    .then(m => { alert(m); selectedRow.remove(); selectedRow = null; })
    .catch(console.error);
  }
</script>
</body>
</html>
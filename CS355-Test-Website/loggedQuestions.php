<?php
require_once 'styleColor.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// DB connection
$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'databaseCS355';
$conn     = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// 1) Get all classes for dropdowns
$class_query  = "SELECT DISTINCT class_name FROM class_competency ORDER BY class_name";
$class_result = $conn->query($class_query);
$allClasses   = [];
if ($class_result) {
    while ($r = $class_result->fetch_assoc()) {
        $allClasses[] = $r['class_name'];
    }
}

// 2) Fetch logged_questions
$sql    = "SELECT 
               question_text, logged_question_id, user_id, 
               class_name, competency_name, class_subject,  
               question_notes, date_added 
           FROM logged_questions 
           ORDER BY date_added DESC";
$result = $conn->query($sql);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>View &amp; Edit Logged Questions</title>
  <link rel="stylesheet" href="style.css">
  <style>
    td.editing-cell { padding: 4px; }
  </style>
</head>
<body>
  <div class="logged_questions">
    <div class="container">
      <div class="left-box">
        <h2>Logged Questions</h2>
        <div class="scrollable-container">
          <table id="questionsTable">
            <thead>
              <tr>
                <th>Question</th><th>User ID</th><th>Class</th>
                <th>Competency</th><th>Subject</th><th>Notes</th><th>Date Added</th>
              </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
              <tr data-id="<?=htmlspecialchars($row['logged_question_id'])?>" onclick="selectQuestion(this)">
                <td><?=htmlspecialchars($row['question_text'])?></td>
                <td><?=htmlspecialchars($row['user_id'])?></td>
                <td><?=htmlspecialchars($row['class_name'])?></td>
                <td><?=htmlspecialchars($row['competency_name'])?></td>
                <td><?=htmlspecialchars($row['class_subject'])?></td>
                <td><?=htmlspecialchars($row['question_notes'])?></td>
                <td><?=htmlspecialchars($row['date_added'])?></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>

        <div class="button-container">
          <button class="large-button modify" onclick="modifyQuestion()">Modify</button>
          <button class="large-button save"   onclick="saveEdit()"   style="display:none;">Save</button>
          <button class="large-button cancel" onclick="cancelEdit()" style="display:none;">Cancel</button>
          <button class="large-button"       onclick="addFinal()">Add Final</button>
          <button class="large-button"       onclick="deleteQuestion()">Delete</button>
          <button class="large-button"       onclick="window.location='mainscreen.php'">Back</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Expose classes to JS
    const allClasses = <?= json_encode($allClasses, JSON_HEX_TAG) ?>;

    let selectedRow, originalValues;

    function selectQuestion(row) {
      if (document.querySelector('.save').style.display !== 'none') return;
      if (selectedRow) selectedRow.classList.remove("selected");
      selectedRow = row;
      row.classList.add("selected");
    }

    function makeSelect(options, selectedVal) {
      const sel = document.createElement('select');
      sel.innerHTML = '<option value="">— select —</option>';
      options.forEach(v => {
        const o = document.createElement('option');
        o.value = o.textContent = v;
        if (v === selectedVal) o.selected = true;
        sel.appendChild(o);
      });
      return sel;
    }

    function modifyQuestion() {
      if (!selectedRow) { alert("Select a question."); return; }
      originalValues = {};
      [0,2,3,4,5].forEach(i => {
        const cell = selectedRow.cells[i];
        originalValues[i] = cell.innerText.trim();
        cell.classList.add("editing-cell");
        if (i === 2) {
          const sel = makeSelect(allClasses, cell.innerText.trim());
          sel.addEventListener('change', onClassChange);
          cell.innerHTML = '';
          cell.appendChild(sel);
        } else if (i === 3) {
          const sel = document.createElement('select');
          sel.innerHTML = '<option value="">— select —</option>';
          sel.addEventListener('change', onCompetencyChange);
          cell.innerHTML = '';
          cell.appendChild(sel);
        } else if (i === 4) {
          const sel = document.createElement('select');
          sel.innerHTML = '<option value="">— select —</option>';
          cell.innerHTML = '';
          cell.appendChild(sel);
        } else {
          cell.contentEditable = true;
        }
      });
      selectedRow.cells[2].firstChild.dispatchEvent(new Event('change'));
      document.querySelector('.modify').style.display = 'none';
      document.querySelector('.save').style.display   = '';
      document.querySelector('.cancel').style.display = '';
    }

    function onClassChange(e) {
      const cls = encodeURIComponent(e.target.value);
      const compCell = selectedRow.cells[3];
      const subjCell = selectedRow.cells[4];
      compCell.firstChild.innerHTML = '<option>— select —</option>';
      subjCell.firstChild.innerHTML = '<option>— select —</option>';
      if (!cls) return;
      fetch(`getCompetencies.php?class=${cls}`)
        .then(r => r.json())
        .then(d => {
          d.competencies.forEach(c => {
            const o = document.createElement('option');
            o.value = o.textContent = c;
            if (c === originalValues[3]) o.selected = true;
            compCell.firstChild.appendChild(o);
          });
          compCell.firstChild.dispatchEvent(new Event('change'));
        })
        .catch(console.error);
    }

    function onCompetencyChange(e) {
      const cls  = encodeURIComponent(selectedRow.cells[2].firstChild.value);
      const comp = encodeURIComponent(e.target.value);
      const subjCell = selectedRow.cells[4];
      subjCell.firstChild.innerHTML = '<option>— select —</option>';
      if (!cls || !comp) return;
      fetch(`getSubjects.php?class=${cls}&competency=${comp}`)
        .then(r => r.json())
        .then(d => {
          d.subjects.forEach(s => {
            const o = document.createElement('option');
            o.value = o.textContent = s;
            if (s === originalValues[4]) o.selected = true;
            subjCell.firstChild.appendChild(o);
          });
        })
        .catch(console.error);
    }

    function cancelEdit() {
      [0,2,3,4,5].forEach(i => {
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
      const vals = [0,2,3,4,5].map(i => {
        const cell = selectedRow.cells[i];
        return (i >= 2 && i <= 4)
          ? cell.firstChild.value.trim()
          : cell.innerText.trim();
      });
      const [qText, cls, comp, subj, notes] = vals;
      const body = `table=logged_questions`
                 + `&id=${id}`
                 + `&question_text=${encodeURIComponent(qText)}`
                 + `&class_name=${encodeURIComponent(cls)}`
                 + `&competency_name=${encodeURIComponent(comp)}`
                 + `&class_subject=${encodeURIComponent(subj)}`
                 + `&question_notes=${encodeURIComponent(notes)}`;

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
        body: "table=logged_questions&id=" + id
      })
      .then(r => r.text())
      .then(m => { alert(m); selectedRow.remove(); selectedRow = null; })
      .catch(console.error);
    }

    function addFinal() {
      if (!selectedRow) { alert("Select a question."); return; }
      const id = encodeURIComponent(selectedRow.dataset.id);
      fetch("addFinal.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: "id=" + id
      })
      .then(r => r.text())
      .then(msg => {
        alert(msg);
        if (msg.includes("successfully added")) {
          selectedRow.remove();
          selectedRow = null;
        }
      })
      .catch(console.error);
    }
  </script>
</body>
</html>

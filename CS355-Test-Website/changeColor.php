<?php
session_start();

// Database connection settings (matching initializeDatabase.php)
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

// Connect to MySQL
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    error_log("Connection failed in changeColor.php: " . $conn->connect_error);
    $conn = null; // Set to null to handle gracefully
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Create user_colors table if it doesn't exist
if ($conn) {
    $sql = "
        CREATE TABLE IF NOT EXISTS user_colors (
            user_id INT PRIMARY KEY,
            far_bg_color VARCHAR(7) DEFAULT '#FFFFFF',
            bg_color VARCHAR(7) DEFAULT '#ADD8E6',
            btn_color VARCHAR(7) DEFAULT '#054154',
            text_color VARCHAR(7) DEFAULT '#2F4F4F',
            back_btn_color VARCHAR(7) DEFAULT '#FF6347',
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )
    ";
    if ($conn->query($sql) !== TRUE) {
        error_log("Error creating user_colors table: " . $conn->error);
    }
}

// Retrieve user's color preferences
$colors = [
    'far_bg_color' => '#FFFFFF',
    'bg_color' => '#ADD8E6',
    'btn_color' => '#054154',
    'text_color' => '#2F4F4F',
    'back_btn_color' => '#FF6347'
];

if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM user_colors WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($colorData = $result->fetch_assoc()) {
            $colors = $colorData;
        } else {
            // Initialize default colors for new user
            $stmt = $conn->prepare("
                INSERT INTO user_colors (user_id, far_bg_color, bg_color, btn_color, text_color, back_btn_color)
                VALUES (?, '#FFFFFF', '#ADD8E6', '#054154', '#2F4F4F', '#FF6347')
            ");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    if (isset($_POST['far_bg_color'], $_POST['bg_color'], $_POST['btn_color'], $_POST['text_color'], $_POST['back_btn_color'])) {
        $far_bg_color = $_POST['far_bg_color'];
        $bg_color = $_POST['bg_color'];
        $btn_color = $_POST['btn_color'];
        $text_color = $_POST['text_color'];
        $back_btn_color = $_POST['back_btn_color'];

        // Update colors in database
        $stmt = $conn->prepare("
            INSERT INTO user_colors (user_id, far_bg_color, bg_color, btn_color, text_color, back_btn_color)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                far_bg_color = ?, bg_color = ?, btn_color = ?, text_color = ?, back_btn_color = ?
        ");
        if ($stmt) {
            $stmt->bind_param("issssssssss", $user_id, $far_bg_color, $bg_color, $btn_color, $text_color, $back_btn_color,
                $far_bg_color, $bg_color, $btn_color, $text_color, $back_btn_color);
            $stmt->execute();
            $stmt->close();

            // Update $colors for immediate display
            $colors = [
                'far_bg_color' => $far_bg_color,
                'bg_color' => $bg_color,
                'btn_color' => $btn_color,
                'text_color' => $text_color,
                'back_btn_color' => $back_btn_color
            ];
        }
    }
    if (isset($_POST['reset'])) {
        // Reset colors to default
        $stmt = $conn->prepare("
            INSERT INTO user_colors (user_id, far_bg_color, bg_color, btn_color, text_color, back_btn_color)
            VALUES (?, '#FFFFFF', '#ADD8E6', '#054154', '#2F4F4F', '#FF6347')
            ON DUPLICATE KEY UPDATE
                far_bg_color = '#FFFFFF', bg_color = '#ADD8E6', btn_color = '#054154',
                text_color = '#2F4F4F', back_btn_color = '#FF6347'
        ");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Update $colors for immediate display
            $colors = [
                'far_bg_color' => '#FFFFFF',
                'bg_color' => '#ADD8E6',
                'btn_color' => '#054154',
                'text_color' => '#2F4F4F',
                'back_btn_color' => '#FF6347'
            ];
            echo "<p style='color: blue;'>Colors reset to default.</p>";
        }
    }
}

// Assign colors for HTML
$farBgColor = $colors['far_bg_color'];
$bgColor = $colors['bg_color'];
$btnColor = $colors['btn_color'];
$textColor = $colors['text_color'];
$backBtnColor = $colors['back_btn_color'];

// Close connection
if ($conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Colors</title>
    <link rel="stylesheet" href="style.css">
    <?php require_once 'styleColor.php'; ?>
</head>
<body>
    <div class="main">
        <h1>Customize Colors</h1>
        <form method="post" class="form-container">
            <label for="far_bg_color">Far Background Color:</label>
            <input type="color" id="far_bg_color" name="far_bg_color" value="<?php echo htmlspecialchars($farBgColor); ?>">
            
            <label for="bg_color">Box Background Color:</label>
            <input type="color" id="bg_color" name="bg_color" value="<?php echo htmlspecialchars($bgColor); ?>">
            
            <label for="btn_color">Main Button Color:</label>
            <input type="color" id="btn_color" name="btn_color" value="<?php echo htmlspecialchars($btnColor); ?>">
            
            <label for="text_color">Text Color:</label>
            <input type="color" id="text_color" name="text_color" value="<?php echo htmlspecialchars($textColor); ?>">
            
            <label for="back_btn_color">Alternate Button Color:</label>
            <input type="color" id="back_btn_color" name="back_btn_color" value="<?php echo htmlspecialchars($backBtnColor); ?>">
            
            <div class="button-container">
                <button type="submit" class="submit-button">Apply Colors</button>
                <button type="submit" name="reset" class="back-button">Reset to Default</button>
                <button type="button" class="submit-button" onclick="location.href='mainscreen.php'">Home</button>
            </div>
        </form>
    </div>
</body>
</html>
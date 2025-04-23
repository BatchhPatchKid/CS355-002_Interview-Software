<?php
session_start();

// Database connection settings (matching initializeDatabase.php)
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

// Default colors (match style.css defaults for consistency)
$defaultFarBgColor = '#FFFFFF';
$defaultBgColor = '#ADD8E6';
$defaultBtnColor = '#054154';
$defaultTextColor = '#2F4F4F';
$defaultBackBtnColor = '#FF6347';

// Initialize colors to defaults
$farBgColor = $defaultFarBgColor;
$bgColor = $defaultBgColor;
$btnColor = $defaultBtnColor;
$textColor = $defaultTextColor;
$backBtnColor = $defaultBackBtnColor;

// Connect to MySQL only if user is logged in
if (isset($_SESSION['user_id'])) {
    $conn = new mysqli($host, $user, $password, $dbname);
    if (!$conn->connect_error) {
        $user_id = (int)$_SESSION['user_id'];

        // Check if user_colors table exists
        $result = $conn->query("SHOW TABLES LIKE 'user_colors'");
        if ($result->num_rows > 0) {
            // Retrieve user's color preferences
            $result = $conn->query("SELECT * FROM user_colors WHERE user_id = $user_id");
            if ($result && $colors = $result->fetch_assoc()) {
                $farBgColor = $colors['far_bg_color'];
                $bgColor = $colors['bg_color'];
                $btnColor = $colors['btn_color'];
                $textColor = $colors['text_color'];
                $backBtnColor = $colors['back_btn_color'];
            }
        }
        $conn->close();
    } else {
        // Log error instead of dying
        error_log("Database connection failed in styleColor.php: " . $conn->connect_error);
    }
}
?>

<style>
    /* Override style.css */
    html body {
        background-color: <?php echo $farBgColor; ?> !important;
    }
    div.main, div.left-box, div.right-box, div.button-box, div.form-container {
        background-color: <?php echo $bgColor; ?> !important;
    }
    button, a, button.question-button, button.large-button, button.submit-button {
        background-color: <?php echo $btnColor; ?> !important;
        color: white;
    }
    button.back-button {
        background-color: <?php echo $backBtnColor; ?> !important;
        color: white;
    }
    div.left-box, div.right-box, label, .add-question label, .logged_questions td {
        color: <?php echo $textColor; ?> !important;
    }
    h1, .logged_questions th {
        color: <?php echo $textColor; ?> !important;
    }
    .logged_questions th {
        background-color: <?php echo $btnColor; ?> !important;
        color: white;
    }
    .logged_questions tr.selected td {
        background-color: <?php echo $textColor; ?> !important;
        color: white !important;
    }
    div.button-container {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin: 20px 0;
    }
    div.button-container button {
        flex: 1;
    }
</style>
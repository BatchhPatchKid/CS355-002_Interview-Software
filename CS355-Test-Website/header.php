<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['username'])) {
    echo '
    <div style="
        position: fixed;
        top: 0.3px;
        left: 0;
        width: 100%;
        background-color: green;
        color: white;
        text-align: center;
        padding: 2px;
        z-index: 1000;
        font-size: 12px;
        line-height: 12px;
        ">
        <div style="line-height: 12px;">Logged in as: ' . htmlspecialchars($_SESSION['username']) . '</div>
        <form action="logout.php" method="POST" style="display: inline; margin: 0; padding: 0;">
            <button type="submit" style="
                background: none;
                border: none;
                font-size: 12px;
                line-height: 12px;
                text-decoration: underline;
                cursor: pointer;
                margin: 0;
                padding: 2px;
                display: inline-block;
                width: auto;
                ">
                Logout
            </button>
        </form>
    </div>
    <div style="height: 20px;"></div>
    ';
}
?>
<!-- Protect Senstive Pages -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page
        header('Location: ../login.php');
        exit;
    }
}

function checkAdmin()
{
    checkLogin();
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
        // Redirect to unauthorized access page or homepage
        header('Location: ../index.php');
        exit;
    }
}
?>

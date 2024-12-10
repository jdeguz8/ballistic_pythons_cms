<?php
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate the snake_id
$snake_id = isset($_GET['snake_id']) ? intval($_GET['snake_id']) : 0;

if ($snake_id > 0) {
    // Initialize cart if not already done
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add the snake to the cart
    if (!in_array($snake_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $snake_id;
        $_SESSION['success_message'] = "Snake added to cart!";
    } else {
        $_SESSION['info_message'] = "Snake is already in the cart.";
    }
} else {
    $_SESSION['error_message'] = "Invalid snake ID.";
}

// Redirect back to the previous page or to index.php if HTTP_REFERER is missing
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header('Location: ' . $redirect_url);
exit;

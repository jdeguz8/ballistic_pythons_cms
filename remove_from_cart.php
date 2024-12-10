<?php
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate snake_id from the GET parameter
$snake_id = isset($_GET['snake_id']) ? intval($_GET['snake_id']) : 0;

if ($snake_id > 0 && isset($_SESSION['cart'])) {
    // Search for the snake ID in the cart
    $key = array_search($snake_id, $_SESSION['cart']);
    if ($key !== false) {
        // Remove the item from the cart
        unset($_SESSION['cart'][$key]);

        // Reindex the cart array to maintain sequential keys
        $_SESSION['cart'] = array_values($_SESSION['cart']);

        $_SESSION['success_message'] = "Snake removed from cart.";
    } else {
        $_SESSION['error_message'] = "Snake not found in cart.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

// Redirect to the cart page
header('Location: cart.php');
exit;

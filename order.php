<?php
// order.php (Processing script)
session_start();
require_once 'includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect selected snake IDs
    $snake_ids = isset($_POST['snake_ids']) ? $_POST['snake_ids'] : [];

    if (empty($snake_ids)) {
        // Handle error
        $_SESSION['error'] = 'Please select at least one snake.';
        header('Location: order_form.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Insert into orders table
        $stmt = $pdo->prepare('
            INSERT INTO orders (customer_id, order_status, order_date, total_amount)
            VALUES (:customer_id, :order_status, NOW(), :total_amount)
        ');
        $total_amount = 0;
        foreach ($snake_ids as $snake_id) {
            // Fetch snake price
            $stmt_snake = $pdo->prepare('SELECT price FROM snakes WHERE snake_id = :snake_id');
            $stmt_snake->execute(['snake_id' => $snake_id]);
            $snake = $stmt_snake->fetch();
            $total_amount += $snake['price'];
        }

        $stmt->execute([
            'customer_id'  => $_SESSION['user_id'],
            'order_status' => 'pending',
            'total_amount' => $total_amount
        ]);

        $order_id = $pdo->lastInsertId();

        // Insert into order_snakes table
        $stmt = $pdo->prepare('INSERT INTO order_snakes (order_id, snake_id) VALUES (:order_id, :snake_id)');
        foreach ($snake_ids as $snake_id) {
            $stmt->execute([
                'order_id' => $order_id,
                'snake_id' => $snake_id
            ]);
        }

        $pdo->commit();

        $_SESSION['success'] = 'Order placed successfully.';
        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to place order: ' . $e->getMessage();
        header('Location: order_form.php');
        exit;
    }
}
?>
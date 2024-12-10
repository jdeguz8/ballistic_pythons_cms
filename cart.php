<?php
// Start session and initialize cart if necessary
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/connect.php';

// Ensure cart is always initialized
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart items
$cart = array_map('intval', $_SESSION['cart']); // Ensure IDs are integers

$snakes_in_cart = [];
if (!empty($cart)) {
    // Prepare SQL query to fetch snake details for items in the cart
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT * FROM snakes WHERE snake_id IN ($placeholders)");
    
    try {
        $stmt->execute($cart);
        $snakes_in_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching cart items: " . $e->getMessage());
    }
}

include 'templates/header.php';
?>

<div class="container mt-5">
    <h1>Your Cart</h1>
    <?php if (!empty($snakes_in_cart)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Snake</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($snakes_in_cart as $snake): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($snake['name']); ?></td>
                        <td>$<?php echo number_format(htmlspecialchars($snake['price']), 2); ?></td>
                        <td>
                            <a href="remove_from_cart.php?snake_id=<?php echo $snake['snake_id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mt-4">
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Your cart is empty.</div>
        <a href="index.php" class="btn btn-primary mt-3">Back to Shopping</a>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>

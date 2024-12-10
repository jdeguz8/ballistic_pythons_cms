<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/connect.php';

// Fetch user information from the database
$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = :user_id');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

include 'templates/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Info</title>
</head>
<body>
<div class="container mt-5">
    <h1>Welcome, <?php echo htmlspecialchars($user['first_name']); ?></h1>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <!-- Add more user details as needed -->
    <!-- Optionally, add links to edit profile or view order history -->
</div>

<?php include 'templates/footer.php'; ?>    
</body>
</html>
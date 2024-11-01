<?php
require_once 'includes/connect.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email']);
    $password          = $_POST['password'];

    if (empty($username_or_email) || empty($password)) {
        $errors[] = 'Please enter your username/email and password.';
    } else {
        // Fetch user from database
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username_or_email OR email = :username_or_email');
        $stmt->execute(['username_or_email' => $username_or_email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, start a session
            session_regenerate_id(true); // Prevent session fixation attacks
            $_SESSION['user_id']    = $user['user_id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];

            // Redirect to appropriate dashboard
            if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $errors[] = 'Invalid username/email or password.';
        }
    }
}
?>
<?php include 'templates/header.php'; ?>

<div class="container mt-5">
    <h2>Login</h2>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username_or_email">Username or Email:</label>
            <input type="text" name="username_or_email" class="form-control" required value="<?php echo htmlspecialchars($username_or_email ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>

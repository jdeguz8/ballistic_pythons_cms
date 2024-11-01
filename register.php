<!-- register.php -->
<?php
require_once 'includes/connect.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $username     = trim($_POST['username']);
    $email        = trim($_POST['email']);
    $password     = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_pass)) {
        $errors[] = 'Please fill in all required fields.';
    }

    if ($password !== $confirm_pass) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if username or email already exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username OR email = :email');
    $stmt->execute(['username' => $username, 'email' => $email]);
    $userExists = $stmt->fetchColumn();

    if ($userExists) {
        $errors[] = 'Username or email already exists.';
    }

    if (empty($errors)) {
        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $stmt = $pdo->prepare('
            INSERT INTO users (username, email, password, first_name, last_name, role)
            VALUES (:username, :email, :password, :first_name, :last_name, :role)
        ');
        $stmt->execute([
            'username'   => $username,
            'email'      => $email,
            'password'   => $passwordHash,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => 'customer' // Default role
        ]);

        // Redirect to login page with success message
        $_SESSION['success'] = 'Registration successful! You can now log in.';
        header('Location: login.php');
        exit;
    }
}
?>
<?php include 'templates/header.php'; ?>

<div class="container mt-5">
    <h2>Register</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <!-- Form fields -->
        <div class="form-group">
            <label for="username">Username<span class="text-danger">*</span>:</label>
            <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="email">Email<span class="text-danger">*</span>:</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="password">Password<span class="text-danger">*</span>:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password<span class="text-danger">*</span>:</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <!-- Optional fields -->
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>

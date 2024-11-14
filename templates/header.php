<!-- templates/header.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ballistic Pythons</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Your CSS and meta tags -->
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Ballistic Pythons</a>
    <!-- Navbar toggler omitted for brevity -->
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php">Admin Dashboard</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <span class="navbar-text">
                        Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username']); ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            <?php endif; ?>
            <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
        <input class="form-control mr-sm-2" type="search" name="q" placeholder="Search snakes" aria-label="Search" required>
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
        </ul>
    </div>
</nav>

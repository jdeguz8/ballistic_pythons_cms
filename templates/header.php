<!-- templates/header.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/connect.php';

// Fetch morphs for the dropdown menu
$stmt = $pdo->query('SELECT * FROM morphs ORDER BY name ASC');
$morphs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ballistic Pythons</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include your custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">Ballistic Pythons</a>
        <!-- Navbar Toggler (for mobile view) -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" 
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Navbar Links and Dropdowns -->
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <!-- Left-aligned navigation items -->
            <ul class="navbar-nav mr-auto">
                <!-- Home Link -->
                <li class="nav-item active">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <!-- Morphs Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="morphsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Morphs
                    </a>
                    <div class="dropdown-menu" aria-labelledby="morphsDropdown">
                        <?php foreach ($morphs as $morphItem): ?>
                            <a class="dropdown-item" href="morph.php?id=<?php echo $morphItem['morph_id']; ?>">
                                <?php echo htmlspecialchars($morphItem['name']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                <!-- Add more navigation items here if needed -->
            </ul>
            <!-- Right-aligned navigation items -->
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Display for logged-in users -->
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <span class="navbar-text">
                            Welcome, <a href="<?php echo ($_SESSION['role'] === 'admin') ? 'admin/dashboard.php' : 'customer.php'; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username']); ?>
                            </a>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <!-- Display for guests -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
            <!-- Search Form -->
            <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                <input class="form-control mr-sm-2" type="search" name="q" placeholder="Search snakes" aria-label="Search" required>
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
            </form>
        </div>
    </nav>

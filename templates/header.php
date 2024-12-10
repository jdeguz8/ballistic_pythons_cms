<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/connect.php';

// Ensure `cart` is always initialized as an array
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


// Fetch morphs for the dropdown menu
$stmt = $pdo->query('SELECT * FROM morphs ORDER BY name ASC');
$morphs = $stmt->fetchAll();

?>

<meta charset="UTF-8">
<title>Ballistic Pythons</title>
<!-- CSS Files -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/styles.css">
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
<!-- Fancybox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <!-- Navbar Toggler (for mobile view) -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <!-- Collapsible content -->
    <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
        <!-- Centered navigation items -->
        <ul class="navbar-nav">
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
                        <a class="dropdown-item" href="morph.php?id=<?php echo htmlspecialchars($morphItem['morph_id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($morphItem['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </li>
            <!-- Dashboard Link for Admin/Staff -->
            <?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
                </li>
            <?php endif; ?>
            <!-- Welcome Message -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <span class="navbar-text">
                        Welcome, 
                        <a href="<?php echo ($_SESSION['role'] === 'admin') ? 'admin/dashboard.php' : 'customer.php'; ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </span>
                </li>
                <!-- Logout Link -->
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <!-- Login and Register Links for Guests -->
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            <?php endif; ?>
            <!-- Cart Link -->
            <li class="nav-item">
                <?php
                $cartCount = count($_SESSION['cart']);
                ?>
                <a class="nav-link" href="cart.php">Cart (<?php echo $cartCount; ?>)</a>
            </li>
            <!-- Search Form -->
            <li class="nav-item position-relative">
                <form class="form-inline my-2 my-lg-0" action="search.php" method="GET" autocomplete="off">
                    <input
                        class="form-control mr-sm-2"
                        type="search"
                        name="q"
                        id="search-input"
                        placeholder="Search snakes"
                        aria-label="Search"
                        required
                    >
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                    <!-- Autocomplete List -->
                    <div id="autocomplete-list" class="list-group"></div>
                </form>
            </li>
        </ul>
    </div>
</nav>


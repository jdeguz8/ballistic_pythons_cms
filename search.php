<?php
require_once 'includes/connect.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($search_query)) {
    header('Location: index.php');
    exit;
}

// Define pagination variables
$results_per_page = 9;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $results_per_page;

// Prepare search query
$sql = '
    SELECT snakes.*, 
           morphs.name AS morph_name, 
           GROUP_CONCAT(DISTINCT traits.name ORDER BY traits.name SEPARATOR ", ") AS trait_names,
           GROUP_CONCAT(DISTINCT traits.trait_id ORDER BY traits.name SEPARATOR ",") AS trait_ids
    FROM snakes
    JOIN morphs ON snakes.morph_id = morphs.morph_id
    LEFT JOIN snake_traits ON snakes.snake_id = snake_traits.snake_id
    LEFT JOIN traits ON snake_traits.trait_id = traits.trait_id
    WHERE snakes.availability_status = "available"
      AND (snakes.name LIKE :search_query1
           OR snakes.species LIKE :search_query2
           OR morphs.name LIKE :search_query3
           OR traits.name LIKE :search_query4)
    GROUP BY snakes.snake_id
    ORDER BY snakes.date_added DESC
    LIMIT ' . intval($results_per_page) . ' OFFSET ' . intval($offset);

$stmt = $pdo->prepare($sql);
$like_query = '%' . $search_query . '%';
$stmt->bindValue(':search_query1', $like_query, PDO::PARAM_STR);
$stmt->bindValue(':search_query2', $like_query, PDO::PARAM_STR);
$stmt->bindValue(':search_query3', $like_query, PDO::PARAM_STR);
$stmt->bindValue(':search_query4', $like_query, PDO::PARAM_STR);
$stmt->execute();
$snakes = $stmt->fetchAll();



// Get total number of results
$count_sql = '
    SELECT COUNT(DISTINCT snakes.snake_id)
    FROM snakes
    JOIN morphs ON snakes.morph_id = morphs.morph_id
    LEFT JOIN snake_traits ON snakes.snake_id = snake_traits.snake_id
    LEFT JOIN traits ON snake_traits.trait_id = traits.trait_id
    WHERE snakes.availability_status = "available"
      AND (snakes.name LIKE :search_query1
           OR snakes.species LIKE :search_query2
           OR morphs.name LIKE :search_query3
           OR traits.name LIKE :search_query4)
';

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->bindValue(':search_query1', $like_query, PDO::PARAM_STR);
$count_stmt->bindValue(':search_query2', $like_query, PDO::PARAM_STR);
$count_stmt->bindValue(':search_query3', $like_query, PDO::PARAM_STR);
$count_stmt->bindValue(':search_query4', $like_query, PDO::PARAM_STR);
$count_stmt->execute();
$total_results = $count_stmt->fetchColumn();
$total_pages = ceil($total_results / $results_per_page);

include 'templates/header.php';
?>

<div class="container mt-5">
    <h1 class="mb-4">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
    <?php if ($snakes): ?>
    <div class="row">
        <?php foreach ($snakes as $snake): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <?php if (!empty($snake['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($snake['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($snake['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($snake['name']); ?></h5>
                        <p class="card-text">
                            <?php echo htmlspecialchars($snake['species']); ?> - <?php echo htmlspecialchars($snake['morph_name']); ?>
                        </p>
                        <?php if (!empty($snake['trait_names'])): ?>
                            <p class="card-text">
                                Traits: <?php echo htmlspecialchars($snake['trait_names']); ?>
                            </p>
                        <?php endif; ?>
                        <p class="card-text">$<?php echo number_format($snake['price'], 2); ?></p>
                        <a href="snake_details.php?id=<?php echo $snake['snake_id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- Pagination controls -->
<?php else: ?>
    <p>No results found.</p>
<?php endif; ?>

        <!-- Pagination controls -->
        <nav aria-label="Page navigation">
          <ul class="pagination">
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
</div>

<?php include 'templates/footer.php'; ?>

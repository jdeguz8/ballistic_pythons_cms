<?php
// test_db.php

require_once 'includes/connect.php';

try {
    $stmt = $pdo->query('SELECT * FROM morphs');
    $morphs = $stmt->fetchAll();

    echo '<pre>';
    print_r($morphs);
    echo '</pre>';
} catch (\PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>

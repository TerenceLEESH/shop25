<?php
// getProductDetails.php
include 'db.php';

$pid = isset($_GET['pid']) ? intval($_GET['pid']) : null;

if ($pid) {
    $stmt = $pdo->prepare("SELECT name, price FROM products WHERE pid = :pid");
    $stmt->execute([':pid' => $pid]);
    
    if ($row = $stmt->fetch()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    echo json_encode(['error' => 'No product ID provided']);
}
?>
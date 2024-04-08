<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div id="brand">
            <h1>Peachmart!!</h1>
        </div>
        <nav id="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php
            $catid = isset($_GET['catid']) ? intval($_GET['catid']) : null;
                if ($catid !== null) {
                    $stmt = $pdo->prepare("SELECT catid, name FROM categories WHERE catid = :catid");
                    $stmt->execute([':catid' => $catid]);
                    
                    if ($stmt->rowCount() > 0) {
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<li><a href="index.php?catid=' . $row["catid"] . '">' . $row["name"] . '</a></li>';
                        }
                    } else {
                        echo "<li>0 results</li>";
                    }
                } else {
                    // Query for all categories if no specific catid is requested
                    $stmt = $pdo->query("SELECT catid, name FROM categories");
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<li><a href="index.php?catid=' . $row["catid"] . '">' . $row["name"] . '</a></li>';
                    }
                }
            ?>
                <!-- ... more categories as needed ... -->
            </ul>
            <div class="nav-cart">
                <div id="cart-icon" class="cart-icon">
                    shopping-list (<span id="cart-count">0</span> items)
                </div>
                <div id="cart-dropdown" class="cart-dropdown">
                    <div id="cart-items" class="cart-items">
                        <div class="cart-icon" onmouseover="showCartDropdown()" onmouseout="hideCartDropdown()">
                            <span>🛒 cart</span>
                            <span id="cart-count">0</span>
                        </div>
                        <div id="cart-dropdown" class="cart-dropdown">
                            <!-- Here you will dynamically insert cart items -->
                        </div>
                    </div>
                    <div id="cart-total" class="cart-total">
                        Total: $<span id="cart-total-amount">0.00</span>
                    </div>
                    <div class="cart-footer">
                        <button id="checkout-button">Checkout</button>
                    </div>
                </div>
            </div>
        </nav>
        
    </header>

<div class="container">
    <!-- Main Product Details -->
    <main class="product-details">
    <?php
// Check if the product_id is set in the URL
// if (isset($_GET['pid'])) {
//     $pid = intval($_GET['pid']);
//     // Prepare a statement to avoid SQL injection
//     $stmt = $conn->prepare("SELECT p.*, pi.image_path FROM products p LEFT JOIN product_image pi ON p.pid = pi.pid WHERE p.pid = ?");
//     $stmt->bind_param("i", $pid);
//     $stmt->execute();
//     $result = $stmt->get_result();
if (isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    $stmt = $pdo->prepare("SELECT p.*, pi.image_path FROM products p LEFT JOIN product_image pi ON p.pid = pi.pid WHERE p.pid = :pid");
    $stmt->execute([':pid' => $pid]);

    // Fetch the product details
    //if ($result->num_rows > 0) {
    if ($stmt->rowCount() > 0) {
        //$row = $result->fetch_assoc();
        // $row = $stmt->fetch();
        // $imagePath = $row["image_path"] ?? 'default_product_image.jpg'; // Use a default image if none is found
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $imagePath = $row["image_path"] ?? 'default_product_image.jpg';

        echo '<div class="product-detail-container">';
        echo '  <div class="product-image">';
        // Assuming the image directory is 'image/' and the images are stored with the path in 'image_path'
        echo '<img src="image/' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row["name"]) . '" style="height:650px; width:auto;">';
        echo '  </div>';
        echo '  <div class="product-info">';
        echo '    <h1>' . htmlspecialchars($row["name"]) . '</h1>';
        echo '    <p class="product-description">' . htmlspecialchars($row["description"]) . '</p>';
        echo '    <p class="product-price">$' . htmlspecialchars($row["price"]) . '</p>';
        echo '  <button class="btn-add-to-cart" data-pid="' . htmlspecialchars($row["pid"]) . '" onclick="addToCart(this)">Add to Cart</button>';
        echo '  </div>';
        echo '</div>';
    } else {
        echo '<p>Product not found.</p>';
    }
    //$stmt->close();
} else {
    echo '<p>No product ID provided.</p>';
}
?>
    </main>
</div>

<script src="cart.js"></script>
</body>
</html>


<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shopping Site</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div id="brand">
            <h1>Peachmart!!</h1>
        </div>
        <div class="user-info">
        <?php
            // Check if the user is an admin and if so, display "Guest"
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
            echo 'Guest';
        } else {
            // If not an admin, check if the email session variable is set
            echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Guest';
        }
        ?>
        </div>
        <nav id="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php
            $sql = "SELECT catid, name FROM categories";
            $stmt = $pdo->query($sql);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<li><a href="index.php?catid=' . $row["catid"] . '">' . $row["name"] . '</a></li>';
                }
            } else {
                echo "0 results";
            }
            $catid = isset($_GET['catid']) ? intval($_GET['catid']) : null;
            if ($catid !== null) {
                $stmt = $pdo->prepare("SELECT p.*, pi.image_path FROM products p LEFT JOIN product_image pi ON p.pid = pi.pid WHERE p.catid = :catid");
                $stmt->execute([':catid' => $catid]);
            } else {
                $stmt = $pdo->query("SELECT p.*, pi.image_path FROM products p LEFT JOIN product_image pi ON p.pid = pi.pid");
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

    <main>
    <section id="product-list">
    <?php
        //$sql = isset($_GET['catid']) ? "SELECT p.*, pi.image_path FROM products p LEFT JOIN product_image pi ON p.pid = pi.pid WHERE p.catid = " . intval($_GET['catid']) : "SELECT p.*, pi.image_path FROM products p LEFT JOIN product_image pi ON p.pid = pi.pid";
        //$result = $conn->query($sql);
        $catid = isset($_GET['catid']) ? intval($_GET['catid']) : null;
        if ($catid !== null) {
            $stmt = $pdo->prepare("SELECT p.*, pi.image_path FROM products p LEFT JOIN product_image pi ON p.pid = pi.pid WHERE p.catid = :catid");
            $stmt->execute([':catid' => $catid]);
        } else {
            $stmt = $pdo->query("SELECT p.*, pi.image_path FROM products p LEFT JOIN product_image pi ON p.pid = pi.pid");
        }
        //if ($result->num_rows > 0) {
        if ($stmt->rowCount() > 0) {
            //while ($row = $result->fetch_assoc()) {
            while ($row = $stmt->fetch()) {
                // Creating the link to the product details page
                $productDetailsUrl = "product1.php?pid=" . $row["pid"];
                $imagePath = $row["image_path"] ?? 'default_product_image.jpg'; 
        
                echo '<div class="product-card">';
                echo '  <a href="' . htmlspecialchars($productDetailsUrl) . '" class="product-link">';
                echo '    <div class="product-image">';
                echo '      <img src="image/' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row["name"]) . '">';
                echo '    </div>';
                echo '    <div class="product-info">';
                echo '      <h5 class="product-name">' . htmlspecialchars($row["name"]) . '</h5>';
                echo '    </div>';
                echo '  </a>';
                echo '  <p class="product-price">$' . htmlspecialchars($row["price"]) . '</p>';
                // Change the onclick event to call addToCart with the product ID as a string
                //echo '  <button class="btn-add-to-cart" onclick="addToCart(\'' . htmlspecialchars($row["pid"]) . '\')">Add to Cart</button>';
                echo '  <button class="btn-add-to-cart" data-pid="' . htmlspecialchars($row["pid"]) . '" onclick="addToCart(this)">Add to Cart</button>';
                echo '</div>';
            }
        } else {
            echo "<p>No products found.</p>";
        }
    ?>
</section>
    </main>

    <aside id="shopping-list" class="hidden">
        <!-- Shopping list content with input boxes, quantities, and checkout button -->
    </aside>

    <footer>
        <p>&copy; 2024 My Shopping Website. All rights reserved.</p>
    </footer>

    <script src="cart.js"></script>
    <script>
        // Make sure to call renderCart on page load if needed
        document.addEventListener('DOMContentLoaded', function() {
            let cart = getCart();
            renderCart(cart);
        });
    </script>
</body>
</html>
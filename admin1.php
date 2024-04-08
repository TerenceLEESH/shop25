<?php
include 'db.php'; 

function isValidAuthToken($token) {
    return isset($_SESSION['auth_token']) && hash_equals($_SESSION['auth_token'], $token);
}

// Redirect if the auth token is not valid
if (!isset($_COOKIE['auth']) || !isValidAuthToken($_COOKIE['auth'])) {
    header('Location: login.php');
    exit;
}

if (!$pdo) {
    die("Connection failed: " . $pdo->errorInfo());
}
$nonce = generateNonce(); // Generate a new nonce for the form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Control Panel</title>
</head>
<body> 
    <div class="user-info">
        <?php  
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
            echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Guest';
        } else {
            // If not an admin, check if the email session variable is set
            echo 'Guest';
        }
        ?>
        
        <a href="admin1-process.php?action=logout">Logout</a>
        <a href="change_password.php">Change Password</a>
    </div>  
    <h1>Admin Control Panel</h1>
    <form action="admin1-process.php?action=product_insert" method="post" enctype="multipart/form-data">
    <!-- New Product Form -->
    <fieldset>
        <legend>New Product</legend>
        <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
            <label for="product_catid">Category *</label>
            <div>
                <select id="product_catid" name="catid">
                <?php
                $stmt = $pdo->query("SELECT catid, name FROM categories");

                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($row["catid"]) . "'>" . htmlspecialchars($row["name"]) . "</option>";
                    }
                } else {
                    echo "<option>No categories available</option>";
                }
                ?>
                </select>
            </div>
            <label for="product_name">Name *</label>
            <div><input type="text" id="product_name" name="name" required></div>
            <label for="product_price">Price *</label>
            <div><input type="number" id="product_price" name="price" required></div>
            <label for="product_description">Description</label>
            <div><textarea id="product_description" name="description"></textarea></div>
            <label for="product_image">Image *</label>
            <div>
            <label for="add_product_image">Image (jpg/gif/png, <= 10MB) *</label>
            <input type="file" id="add_product_image" name="image" accept="image/jpeg, image/gif, image/png" required>
            </div>
            <input type="submit" value="Submit">
        </form>
    </fieldset>

    <!-- Delete Products by catid Form -->
    <fieldset>
        <legend>Delete Products by pid</legend>
        <form id="product_delete" method="POST" action="admin1-process.php?action=product_delete">
            <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
            <label for="delete_pid">Category *</label>
            <div>
                <select id="delete_pid" name="pid">             
                    <?php
                    $stmt = $pdo->query("SELECT pid, name FROM products");
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($row["pid"]) . "'>" . htmlspecialchars($row["name"]) . "</option>";
                        }
                    } else {
                        echo "<option>No products available</option>";
                    }               
                    ?>
                </select>
            </div>
            <input type="submit" value="Delete Products">
        </form>
    </fieldset>

    <!-- New Category Form -->
    <fieldset>
        <legend>New Category</legend>
        <form id="category_insert" method="POST" action="admin1-process.php?action=category_insert">
            <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
            <label for="category_name">Name *</label>
            <div><input type="text" id="category_name" name="name" required></div>
            <input type="submit" value="Submit">
        </form>
    </fieldset>

    <!-- Edit Category Form -->
    <fieldset>
        <legend>Edit Category</legend>
        <form id="category_edit" method="POST" action="admin1-process.php?action=category_edit">
            <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
            <label for="edit_category_catid">Category *</label>
            <div>
            <select id="edit_category_catid" name="catid">
                <?php
                $stmt = $pdo->query("SELECT catid, name FROM categories");
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($row["catid"]) . "'>" . htmlspecialchars($row["name"]) . "</option>";
                    }
                } else {
                    echo "<option>No categories available</option>";
                }
                
                ?>
            </select> 
                
            </div>
            <label for="edit_category_name">New Name *</label>
            <div><input type="text" id="edit_category_name"name="new_name" required></div>
            <input type="submit" value="Update">
        </form>
    </fieldset>

    <!-- Delete Category Form -->
    <fieldset>
    <legend>Delete Category</legend>
    <form id="category_delete" method="POST" action="admin1-process.php?action=category_delete">
        <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
        <label for="delete_category_catid">Category *</label>
        <div>
            <select id="delete_category_catid" name="catid">
                <?php   
                $stmt = $pdo->query("SELECT catid, name FROM categories");
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($row["catid"]) . "'>" . htmlspecialchars($row["name"]) . "</option>";
                    }
                } else {
                    echo "<option>No categories available</option>";
                }
                ?>
            </select>
        </div>
        <input type="submit" value="Delete Category">
    </form>
</fieldset>
</body>
</html>
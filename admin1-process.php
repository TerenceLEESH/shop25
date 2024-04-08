<?php
include 'db.php'; // Use the database connection from 'db.php'

function isValidAuthToken($token) {
    return isset($_SESSION['auth_token']) && hash_equals($_SESSION['auth_token'], $token);
}

// Redirect or exit if the auth token is not valid
if (!isset($_COOKIE['auth']) || !isValidAuthToken($_COOKIE['auth'])) {
    exit('Access denied');
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Clear the authentication token
    unset($_SESSION['auth_token']);

    // Destroy the session
    $_SESSION = array(); // Clear the session array
    session_destroy(); // Destroy the session

    // Clear the auth cookie by setting its expiration to the past
    if (isset($_COOKIE['auth'])) {
        setcookie('auth', '', time() - 3600, '/'); // adjust the path if needed
    }

    // Redirect to the login page or a confirmation page
    header('Location: login.php');
    exit;
}

function handleProductInsert($pdo) {
    $nonce = $_POST['nonce'] ?? '';
    if (!validateNonce($nonce)) {
        die("CSRF token validation failed.");
    }
    $catid = $_POST['catid'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Prepare the SQL statement using named parameters to mitigate SQL injection
    $stmt = $pdo->prepare("INSERT INTO products (catid, name, price, description) VALUES (:catid, :name, :price, :description)");
    
    // Bind the parameters
    $stmt->bindParam(':catid', $catid);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':description', $description);

    if ($stmt->execute()) {
        echo "New Product Inserted:<br>";
        echo "Category ID: $catid<br>";
        echo "Name: $name<br>";
        echo "Price: $price<br>";
        echo "Description: $description<br>";

        // Get the ID of the newly inserted product
        $pid = $pdo->lastInsertId();

        // Now handle the image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'D:/Web2/ass1/image/'; // Ensure this directory exists and is writable
            $file_tmp_name = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = array('jpg', 'jpeg', 'gif', 'png');
        
            // Check file size and extension
            if ($file_size > 10485760) {
                die("File too large.");
            }
            if (!in_array($file_ext, $allowed_ext)) {
                die("Unsupported file format.");
            }
        
            // Generate a unique file name
            $new_file_name = uniqid() . '.' . $file_ext;
            
            // Move the file to the specified directory
            if (move_uploaded_file($file_tmp_name, $upload_dir . $new_file_name)) {
                // Prepare the SQL statement to insert the image
                $stmt = $pdo->prepare("INSERT INTO product_image (pid, image_path) VALUES (:pid, :image_path)");
                
                // Bind parameters to prevent SQL injection
                $stmt->bindParam(':pid', $pid);
                $stmt->bindParam(':image_path', $new_file_name);
                
                // Execute the statement
                if ($stmt->execute()) {
                    echo "Image successfully uploaded and associated with the product.";
                } else {
                    echo "Image uploaded but error occurred when saving to the database.";
                }
            } else {
                die("File upload failed.");
            }
        } else {
            // File upload error
            echo "File upload error. Error code: " . $_FILES['image']['error'];
        }
    } else {
        echo "Error: " . $stmt->errorInfo()[2]; // Get the error info from the statement
    }
}

function handleProductDelete($pdo) {
    $nonce = $_POST['nonce'] ?? '';
    if (!validateNonce($nonce)) {
        die("CSRF token validation failed.");
    }
    $pid = $_POST['pid'];

    $stmtDeleteImages = $pdo->prepare("DELETE FROM product_image WHERE pid = ?");
    $stmtDeleteImages->bindParam(1, $pid);

    $stmtDeleteProduct = $pdo->prepare("DELETE FROM products WHERE pid = ?");
    $stmtDeleteProduct->bindParam(1, $pid);

    try {
        $pdo->beginTransaction(); 

        if ($stmtDeleteImages->execute()) {
            if ($stmtDeleteProduct->execute()) {
                $pdo->commit();  
                echo "Product deleted successfully.";
            } else {
                $pdo->rollBack();  
                echo "Error deleting product.";
            }
        } else {
            $pdo->rollBack();  
            echo "Error deleting product images.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();  
        echo "Error: " . $e->getMessage();
    }
}

function handleCategoryInsert($pdo) {
    $nonce = $_POST['nonce'] ?? '';
    if (!validateNonce($nonce)) {
        die("CSRF token validation failed.");
    }
    $name = $_POST['name'];

    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bindParam(1, $name);

    if ($stmt->execute()) {
        echo "Category created successfully.";
    } else {
        echo "Error creating category.";
    }
}

function handleCategoryEdit($pdo) {
    $nonce = $_POST['nonce'] ?? '';
    if (!validateNonce($nonce)) {
        die("CSRF token validation failed.");
    }
    $catid = $_POST['catid'];
    $new_name = $_POST['new_name'];

    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE catid = ?");
    $stmt->bindParam(1, $new_name);
    $stmt->bindParam(2, $catid);

    if ($stmt->execute()) {
        echo "Category updated successfully.";
    } else {
        echo "Error updating category.";
    }
}

function handleCategoryDelete($pdo) {
    $nonce = $_POST['nonce'] ?? '';
    if (!validateNonce($nonce)) {
        die("CSRF token validation failed.");
    }
    $catid = $_POST['catid'];

    $stmt = $pdo->prepare("DELETE FROM categories WHERE catid = ?");
    $stmt->bindParam(1, $catid);

    if ($stmt->execute()) {
        echo "Category deleted successfully.";
    } else {
        echo "Error deleting category.";
    }
}

// Check if an action is specified
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Perform the appropriate action based on the value of 'action'
    switch ($action) {
        case 'product_insert':
            handleProductInsert($pdo);
            break;
        case 'product_delete':
            handleProductDelete($pdo);
            break;
        case 'category_insert':
            handleCategoryInsert($pdo);
            break;
        case 'category_edit':
            handleCategoryEdit($pdo);
            break;
        case 'category_delete':
            handleCategoryDelete($pdo);
            break;
        default:
            echo "Invalid action.";
            break;
    }
} else {
    echo "No action specified.";
}

// Close the database connection
$pdo = null;
?>

<script>
  // Populate the select dropdown with categories
  var categories = <?php echo json_encode($categories); ?>;
  var select = document.getElementById("delete_category_catid");

  categories.forEach(function(category) {
    var option = document.createElement("option");
    option.value = category.catid;
    option.text = category.category_name;
    select.appendChild(option);
  });
</script>
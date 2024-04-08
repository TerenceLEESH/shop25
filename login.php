<?php

include 'db.php'; 

function generateAuthToken() {
    return bin2hex(openssl_random_pseudo_bytes(32)); // Generate a secure token
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify the CSRF token
function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $submittedToken = $_POST['csrf_token'];

    // Verify CSRF token
    if (!verifyCsrfToken($submittedToken)) {
        die('CSRF token validation failed');
    }
    // Prepare the SQL statement to select the user from the database
    $stmt = $pdo->prepare("SELECT userid, email, password, isAdmin FROM `user` WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Fetch the user from the database
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If user exists
    if ($user) {
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set the session variables
            $_SESSION['userid'] = $user['userid'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['isAdmin'] = $user['isAdmin'];

            // Create a hashed token for the auth cookie
            $auth_token = hash('sha256', random_bytes(32)); // This line should now be reachable
            $expires = time() + (3 * 24 * 60 * 60); // Cookie expires in 3 days

            // Set the auth cookie with HTTPOnly and Secure flags
            setcookie('auth', $auth_token, [
                'expires' => $expires,
                'httponly' => true,
                'secure' => true, // Ensure you have HTTPS setup
                'samesite' => 'Strict' // Optional but recommended
            ]);

            // Store the hashed token in the session or database for validation
            $_SESSION['auth_token'] = $auth_token;

            // Redirect to admin panel or main page
            // ...
            // Redirect to admin panel or main page
            if ($user['isAdmin']) {
                header('Location: admin1.php');
            } else {
                header('Location: index.php');
            }
            session_regenerate_id(true);

            exit;
            
        } else {
            $error = "Incorrect email or password.";
        }
    } else {
        $error = "Incorrect email or password.";
    }

}

// Generate a new CSRF token for the form
$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    

    <!-- Login form -->
    <form method="post" action="login.php">
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>
        <!-- Include the CSRF token as a hidden field in the form -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div>
            <button type="submit">Login</button>
        </div>
    </form>
</body>
</html>
<?php
//session_start(); // Ensure session is started before any output
include 'db.php'; // Use the database connection from 'db.php'

// Check if the user is logged in and redirect to login page if not
if (!isset($_SESSION['auth_token'])) {
    header('Location: login.php');
    exit;
}

// Generate a new nonce for each new page load
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $nonce = generateNonce();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nonce = $_POST['nonce'] ?? '';

    if (!validateNonce($nonce)) {
        die("CSRF token validation failed.");
    }

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    if ($new_password !== $confirm_new_password) {
        die("New passwords do not match.");
    }

    // Fetch the current password hash from the database for the logged-in user
    $stmt = $pdo->prepare("SELECT password FROM user WHERE userid = :userid");
    $stmt->bindParam(':userid', $_SESSION['userid']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current_password, $user['password'])) {
        die("Current password is incorrect.");
    }

    // Hash the new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database
    $update_stmt = $pdo->prepare("UPDATE user SET password = :new_password WHERE userid = :userid");
    $update_stmt->bindParam(':new_password', $new_password_hash);
    $update_stmt->bindParam(':userid', $_SESSION['userid']);
    if ($update_stmt->execute()) {
        // Password updated, now logout the user
        $_SESSION = array();
        session_destroy();
        header('Location: login.php?message=Password+changed.+Please+log+in+again.');
        exit;
    } else {
        die("Failed to update password.");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
</head>
<body>
    <fieldset>
    <legend>Change Password</legend>
    <form id="change_password" method="POST" action="change_password.php">
        <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
        <label for="current_password">Current Password *</label>
        <div><input type="password" id="current_password" name="current_password" required></div>
        <label for="new_password">New Password *</label>
        <div><input type="password" id="new_password" name="new_password" required></div>
        <label for="confirm_new_password">Confirm New Password *</label>
        <div><input type="password" id="confirm_new_password" name="confirm_new_password" required></div>
        <input type="submit" value="Change Password">
    </form>
</fieldset>
</body>
</html>
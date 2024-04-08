<?php
// Include your database connection script
include 'db.php'; // Assumes db.php contains the PDO connection $pdo

// User data
$users = [
    [
        'email' => 'admin@example.com',
        'password' => 'admin_password', // Replace with a strong password
        'isAdmin' => 1
    ],
    [
        'email' => 'user@example.com',
        'password' => 'user_password', // Replace with a strong password
        'isAdmin' => 0
    ]
];

// Prepare the SQL statement
$sql = "INSERT INTO `user` (email, password, isAdmin) VALUES (:email, :password, :isAdmin)";

// Prepare the statement
$stmt = $pdo->prepare($sql);

// Loop through the users and insert them into the database
foreach ($users as $user) {
    // Hash the password
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    
    // Bind the parameters and execute the statement
    $stmt->bindParam(':email', $user['email']);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':isAdmin', $user['isAdmin']);
    
    $stmt->execute();
}

echo "Users inserted successfully.";
?>
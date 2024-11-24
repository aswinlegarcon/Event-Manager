// admin_setup.php
<?php
require_once '../config.php';

// Function to create admin user
function createAdminUser($pdo, $username, $email, $password) {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        // Update existing user to admin
        $stmt = $pdo->prepare("UPDATE users SET is_admin = TRUE WHERE username = ? OR email = ?");
        $result = $stmt->execute([$username, $email]);
        return $result ? "User updated to admin successfully!" : "Failed to update user.";
    } else {
        // Create new admin user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, TRUE)");
        $result = $stmt->execute([$username, $email, $hashed_password]);
        return $result ? "Admin user created successfully!" : "Failed to create admin user.";
    }
}

// Create your first admin user
// IMPORTANT: Change these credentials!
$admin_username = "";
$admin_email = "";
$admin_password = ""; // Change this to a secure password

echo createAdminUser($pdo, $admin_username, $admin_email, $admin_password);
?>
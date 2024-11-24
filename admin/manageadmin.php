<?php
session_start();
require_once '../config.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: ../login.php");
    exit();
}

// Handle making user an admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['make_admin'])) {
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_admin = TRUE WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        $success = "User successfully made admin!";
    }
}

// Handle removing admin privileges
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_admin'])) {
    $user_id = $_POST['user_id'];
    // Prevent removing the last admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = TRUE");
    $admin_count = $stmt->fetchColumn();
    
    if ($admin_count > 1) {
        $stmt = $pdo->prepare("UPDATE users SET is_admin = FALSE WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $success = "Admin privileges removed!";
        }
    } else {
        $error = "Cannot remove the last admin!";
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, username, email, is_admin FROM users ORDER BY username");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Admins</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Manage Administrators</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Admin Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <?php if (!$user['is_admin']): ?>
                                    <button type="submit" name="make_admin" class="btn btn-success btn-sm">Make Admin</button>
                                <?php else: ?>
                                    <button type="submit" name="remove_admin" class="btn btn-danger btn-sm">Remove Admin</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
</body>
</html>
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("Location: ../login.php");
    exit();
}

// Fetch registered members for the event
$event_id = $_GET['event_id'];
$stmt = $pdo->prepare("
    SELECT r.*, u.username, u.email
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ?
");
$stmt->execute([$event_id]);
$members = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Members</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h3>Registered Members</h3>
        <a href="index.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['username']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td><?php echo $member['registration_date']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
